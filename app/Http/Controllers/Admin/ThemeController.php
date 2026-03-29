<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Services\ThemeManager;
use App\Services\ThemeResolver;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class ThemeController extends Controller
{
    private const PACKAGE_SCHEMA_VERSION = 1;

    public function index(Request $request, ThemeResolver $resolver)
    {
        $filters = [
            'q' => trim((string) $request->string('q')),
            'status' => $request->string('status')->toString(),
        ];

        $themes = Theme::query()
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($builder) use ($filters) {
                    $builder
                        ->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('slug', 'like', '%' . $filters['q'] . '%');
                });
            })
            ->when(in_array($filters['status'], Theme::STATUSES, true), fn ($query) => $query->where('status', $filters['status']))
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.temas.index', [
            'themes' => $themes,
            'activeTheme' => $resolver->activeTheme(Theme::SCOPE_CONSOLE),
            'activeConsoleTheme' => $resolver->activeTheme(Theme::SCOPE_CONSOLE),
            'activeSiteTheme' => $resolver->activeTheme(Theme::SCOPE_SITE),
            'activeAuthTheme' => $resolver->activeTheme(Theme::SCOPE_AUTH),
            'previewTheme' => $resolver->previewThemeFor(auth()->user(), Theme::SCOPE_CONSOLE),
            'filters' => $filters,
            'statuses' => Theme::STATUSES,
        ]);
    }

    public function create()
    {
        return view('admin.temas.create', [
            'theme' => new Theme([
                'base_theme' => 'default',
                'status' => Theme::STATUS_DISPONIVEL,
                'application_scopes' => [Theme::SCOPE_GLOBAL],
                'tokens' => [],
                'assets' => [],
                'config_json' => [],
                'is_default' => false,
            ]),
            'statuses' => Theme::STATUSES,
            'baseThemes' => $this->baseThemes(),
            'scopeOptions' => Theme::scopeOptions(),
            'tokenGroups' => $this->tokenGroups(),
            'assetGroups' => $this->assetGroups(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateTheme($request);

        $theme = DB::transaction(function () use ($data, $request) {
            return $this->persist(new Theme(), $data, $request);
        });

        app(ThemeResolver::class)->forgetCache();

        return redirect()
            ->route('admin.temas.edit', $theme)
            ->with('ok', 'Tema criado com sucesso.');
    }

    public function import(Request $request, ThemeResolver $resolver)
    {
        $data = $request->validate([
            'package' => ['required', 'file', 'mimes:zip', 'max:12288'],
            'import_mode' => ['required', Rule::in(['create_copy', 'update_existing'])],
        ], [
            'package.required' => 'Selecione um pacote .zip do tema.',
            'package.mimes' => 'Envie um arquivo .zip válido.',
            'import_mode.in' => 'Selecione um modo de importação válido.',
        ]);

        $tempDir = storage_path('app/tmp/theme-import-' . Str::uuid());
        FileFacade::ensureDirectoryExists($tempDir);

        try {
            $packagePath = $request->file('package')->getRealPath();
            $zip = new ZipArchive();

            if ($zip->open($packagePath) !== true) {
                throw ValidationException::withMessages([
                    'package' => 'Não foi possível abrir o pacote do tema.',
                ]);
            }

            if ($zip->locateName('manifest.json') === false) {
                $zip->close();

                throw ValidationException::withMessages([
                    'package' => 'O pacote não contém manifest.json.',
                ]);
            }

            $zip->extractTo($tempDir);
            $zip->close();

            $manifestPath = $tempDir . DIRECTORY_SEPARATOR . 'manifest.json';
            $manifestRaw = FileFacade::get($manifestPath);
            $manifest = json_decode($manifestRaw, true);

            if (! is_array($manifest)) {
                throw ValidationException::withMessages([
                    'package' => 'O manifest.json do pacote é inválido.',
                ]);
            }

            $schemaVersion = (int) ($manifest['schema_version'] ?? 0);
            if ($schemaVersion !== self::PACKAGE_SCHEMA_VERSION) {
                throw ValidationException::withMessages([
                    'package' => 'A versão do pacote do tema não é suportada.',
                ]);
            }

            $themeData = $manifest['theme'] ?? null;
            if (! is_array($themeData)) {
                throw ValidationException::withMessages([
                    'package' => 'O pacote não contém a definição do tema.',
                ]);
            }

            $normalized = $this->validateImportedThemeData($themeData);
            $slug = Theme::uniqueSlug($normalized['slug'] ?: $normalized['name']);
            $existing = Theme::query()->where('slug', $normalized['slug'])->first();
            $mode = $data['import_mode'];

            if ($mode === 'update_existing' && ! $existing) {
                throw ValidationException::withMessages([
                    'package' => 'Não existe tema com o mesmo slug para atualizar neste ambiente.',
                ]);
            }

            $theme = DB::transaction(function () use ($normalized, $existing, $mode, $tempDir, &$slug) {
                $target = $mode === 'update_existing' && $existing
                    ? $existing
                    : new Theme();

                if ($target->exists) {
                    $slug = $target->slug;
                }

                $storedAssetPaths = [];

                try {
                    $managedAssets = $target->persistedAssets();
                    foreach (Theme::assetKeys() as $field) {
                        $assetReference = data_get($normalized, "assets.{$field}");
                        if (! filled($assetReference)) {
                            continue;
                        }

                        if ($this->isPackagedAssetPath((string) $assetReference)) {
                            if ($target->hasPersistedAsset($field)) {
                                $this->deleteAssetIfStored((string) $target->persistedAssetPath($field));
                            }

                            $stored = $this->storeImportedAsset(
                                $tempDir,
                                (string) $assetReference,
                                "themes/{$field}",
                                $slug
                            );
                            $storedAssetPaths[] = $stored;
                            $managedAssets[$field] = $stored;
                        } else {
                            $managedAssets[$field] = (string) $assetReference;
                        }
                    }

                    $previewImagePath = $target->preview_image_path;
                    $previewReference = data_get($normalized, 'preview_image');
                    if (filled($previewReference)) {
                        if ($this->isPackagedAssetPath((string) $previewReference)) {
                            if ($previewImagePath) {
                                $this->deleteAssetIfStored((string) $previewImagePath);
                            }

                            $previewImagePath = $this->storeImportedAsset(
                                $tempDir,
                                (string) $previewReference,
                                'themes/preview-image',
                                $slug
                            );
                            $storedAssetPaths[] = $previewImagePath;
                        } else {
                            $previewImagePath = (string) $previewReference;
                        }
                    }

                    $tokens = $target->mergeManagedTokens($normalized['tokens']);
                    $assets = $target->mergeManagedAssets($managedAssets);

                    $isCreating = ! $target->exists;
                    $payload = [
                        'name' => $normalized['name'],
                        'slug' => $mode === 'update_existing'
                            ? $target->slug
                            : Theme::uniqueSlug($normalized['slug'] ?: $normalized['name']),
                        'base_theme' => $normalized['base_theme'],
                        'type' => $normalized['type'] ?? null,
                        'status' => $normalized['status'],
                        'description' => $normalized['description'] ?? null,
                        'starts_at' => $normalized['starts_at'] ?? null,
                        'ends_at' => $normalized['ends_at'] ?? null,
                        'is_default' => false,
                        'tokens' => $tokens,
                        'assets' => $assets,
                        'config_json' => $normalized['config_json'] ?? null,
                        'application_scopes' => $normalized['application_scopes'],
                    ];

                    if (Schema::hasColumn('themes', 'created_by')) {
                        $payload['created_by'] = $isCreating ? auth()->id() : $target->created_by;
                    }

                    if (Schema::hasColumn('themes', 'updated_by')) {
                        $payload['updated_by'] = auth()->id();
                    }

                    $target->fill($payload);
                    $target->preview_image_path = $previewImagePath;
                    $target->save();

                    app(ThemeManager::class)->logActivity(
                        $target,
                        $isCreating ? Theme::LOG_ACTION_CREATED : Theme::LOG_ACTION_UPDATED,
                        [
                            'source' => 'package_import',
                            'status' => $target->status,
                            'scopes' => $target->normalizedScopes(),
                        ]
                    );

                    return $target->refresh();
                } catch (\Throwable $e) {
                    foreach ($storedAssetPaths as $path) {
                        $this->deleteAssetIfStored($path);
                    }

                    throw $e;
                }
            });

            $resolver->forgetCache();

            return redirect()
                ->route('admin.temas.edit', $theme)
                ->with('ok', 'Tema importado com sucesso. Nenhum escopo foi ativado automaticamente.');
        } finally {
            FileFacade::deleteDirectory($tempDir);
        }
    }

    public function edit(Theme $tema, ThemeResolver $resolver)
    {
        return view('admin.temas.edit', [
            'theme' => $tema,
            'statuses' => Theme::STATUSES,
            'baseThemes' => $this->baseThemes(),
            'scopeOptions' => Theme::scopeOptions(),
            'tokenGroups' => $this->tokenGroups(),
            'assetGroups' => $this->assetGroups(),
            'activeTheme' => $resolver->activeTheme(Theme::SCOPE_CONSOLE),
            'activeConsoleTheme' => $resolver->activeTheme(Theme::SCOPE_CONSOLE),
            'activeSiteTheme' => $resolver->activeTheme(Theme::SCOPE_SITE),
            'activeAuthTheme' => $resolver->activeTheme(Theme::SCOPE_AUTH),
            'previewTheme' => $resolver->previewThemeFor(auth()->user(), Theme::SCOPE_CONSOLE),
        ]);
    }

    public function export(Theme $tema)
    {
        $tempDir = storage_path('app/tmp/theme-export-' . Str::uuid());
        FileFacade::ensureDirectoryExists($tempDir);
        FileFacade::ensureDirectoryExists($tempDir . DIRECTORY_SEPARATOR . 'assets');

        try {
            $manifest = [
                'schema_version' => self::PACKAGE_SCHEMA_VERSION,
                'exported_at' => now()->toIso8601String(),
                'theme' => [
                    'name' => $tema->name,
                    'slug' => $tema->slug,
                    'base_theme' => $tema->base_theme,
                    'type' => $tema->type,
                    'description' => $tema->description,
                    'status' => $tema->normalizedStatus(),
                    'application_scopes' => $tema->normalizedScopes(),
                    'starts_at' => optional($tema->starts_at)?->toIso8601String(),
                    'ends_at' => optional($tema->ends_at)?->toIso8601String(),
                    'tokens' => $tema->persistedTokens(),
                    'config_json' => $this->exportableConfig($tema),
                    'assets' => [],
                    'preview_image' => null,
                ],
            ];

            foreach ($tema->persistedAssets() as $key => $path) {
                $manifest['theme']['assets'][$key] = $this->copyAssetToPackage($path, $key, $tempDir);
            }

            if ($tema->preview_image_path) {
                $manifest['theme']['preview_image'] = $this->copyAssetToPackage($tema->preview_image_path, 'preview_image', $tempDir);
            }

            FileFacade::put(
                $tempDir . DIRECTORY_SEPARATOR . 'manifest.json',
                json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );

            $zipPath = storage_path('app/tmp/' . ($tema->slug ?: 'tema') . '-theme-package.zip');
            if (FileFacade::exists($zipPath)) {
                FileFacade::delete($zipPath);
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                abort(500, 'Não foi possível gerar o pacote do tema.');
            }

            $files = FileFacade::allFiles($tempDir);
            foreach ($files as $file) {
                $relative = ltrim(Str::after($file->getPathname(), $tempDir), DIRECTORY_SEPARATOR);
                $zip->addFile($file->getPathname(), str_replace(DIRECTORY_SEPARATOR, '/', $relative));
            }

            $zip->close();

            return response()->download($zipPath, ($tema->slug ?: 'tema') . '-theme-package.zip')->deleteFileAfterSend(true);
        } finally {
            FileFacade::deleteDirectory($tempDir);
        }
    }

    public function update(Request $request, Theme $tema, ThemeResolver $resolver)
    {
        $data = $this->validateTheme($request, $tema);

        $theme = DB::transaction(function () use ($tema, $data, $request) {
            return $this->persist($tema, $data, $request);
        });

        $resolver->forgetCache();

        $isPreview = $resolver->previewThemeFor(auth()->user(), Theme::SCOPE_CONSOLE)?->is($theme) ?? false;
        $isActive = $resolver->activeTheme(Theme::SCOPE_CONSOLE)?->is($theme) ?? false;

        $response = back()->with('ok', 'Tema atualizado com sucesso.');

        if (! $isActive && ! $isPreview) {
            $response->with(
                'theme_update_visibility_hint',
                'As alterações foram salvas, mas este tema não está ativo no console. Use Pré-visualizar ou Ativar tema para ver o resultado.'
            );
        }

        return $response;
    }

    public function preview(Theme $tema, ThemeManager $manager)
    {
        abort_if($tema->normalizedStatus() === Theme::STATUS_ARQUIVADO, 422, 'Tema arquivado nao pode ser pre-visualizado.');

        $manager->setPreview($tema);

        return back()->with('ok', 'Preview aplicado apenas na sua sessao de Admin.');
    }

    public function clearPreview(ThemeManager $manager)
    {
        $manager->clearPreview();

        return back()->with('ok', 'Preview encerrado. O console voltou ao tema global ativo.');
    }

    public function activate(Theme $tema, ThemeManager $manager)
    {
        abort_if($tema->normalizedStatus() === Theme::STATUS_ARQUIVADO, 422, 'Tema arquivado nao pode ser ativado.');

        if ($tema->normalizedStatus() === Theme::STATUS_RASCUNHO) {
            $updates = [
                'status' => Theme::STATUS_DISPONIVEL,
            ];

            if (Schema::hasColumn('themes', 'updated_by')) {
                $updates['updated_by'] = auth()->id();
            }

            $tema->update($updates);
        }

        abort_unless($tema->isAvailableFor(Theme::SCOPE_CONSOLE), 422, 'Este tema nao esta disponivel para o console.');

        $manager->activate($tema);

        return back()->with('ok', 'Tema ativado globalmente no console.');
    }

    public function restoreDefault(ThemeManager $manager)
    {
        $manager->restoreDefault();

        return back()->with('ok', 'Tema padrão restaurado no console.');
    }

    public function activateSite(Theme $tema, ThemeManager $manager)
    {
        abort_if($tema->normalizedStatus() === Theme::STATUS_ARQUIVADO, 422, 'Tema arquivado nao pode ser ativado.');

        if ($tema->normalizedStatus() === Theme::STATUS_RASCUNHO) {
            $updates = [
                'status' => Theme::STATUS_DISPONIVEL,
            ];

            if (Schema::hasColumn('themes', 'updated_by')) {
                $updates['updated_by'] = auth()->id();
            }

            $tema->update($updates);
        }

        abort_unless($tema->isAvailableFor(Theme::SCOPE_SITE), 422, 'Este tema nao esta disponivel para o site.');

        $manager->activateForScope($tema, Theme::SCOPE_SITE);

        return back()->with('ok', 'Tema ativado no site.');
    }

    public function activateAuth(Theme $tema, ThemeManager $manager)
    {
        abort_if($tema->normalizedStatus() === Theme::STATUS_ARQUIVADO, 422, 'Tema arquivado nao pode ser ativado.');

        if ($tema->normalizedStatus() === Theme::STATUS_RASCUNHO) {
            $updates = [
                'status' => Theme::STATUS_DISPONIVEL,
            ];

            if (Schema::hasColumn('themes', 'updated_by')) {
                $updates['updated_by'] = auth()->id();
            }

            $tema->update($updates);
        }

        abort_unless($tema->isAvailableFor(Theme::SCOPE_AUTH), 422, 'Este tema nao esta disponivel para o auth/login.');

        $manager->activateForScope($tema, Theme::SCOPE_AUTH);

        return back()->with('ok', 'Tema ativado no auth/login.');
    }

    public function restoreDefaultSite(ThemeManager $manager)
    {
        $manager->restoreDefaultForScope(Theme::SCOPE_SITE);

        return back()->with('ok', 'Tema padrao restaurado no site.');
    }

    public function restoreDefaultAuth(ThemeManager $manager)
    {
        $manager->restoreDefaultForScope(Theme::SCOPE_AUTH);

        return back()->with('ok', 'Tema padrao restaurado no auth/login.');
    }

    public function archive(Theme $tema, ThemeManager $manager, ThemeResolver $resolver)
    {
        abort_unless(auth()->user()?->can('themes.archive') || auth()->user()?->can('themes.activate'), 403);

        $availableCount = Theme::query()
            ->whereNotIn('status', [Theme::STATUS_ARQUIVADO, 'archived'])
            ->count();

        if ($availableCount <= 1) {
            return back()->with('erro', 'Mantenha pelo menos um tema disponivel no sistema.');
        }

        $isActive = $resolver->activeTheme(Theme::SCOPE_CONSOLE)?->is($tema);
        $hasReplacement = Theme::query()
            ->whereKeyNot($tema->id)
            ->whereIn('status', [Theme::STATUS_DISPONIVEL, 'available'])
            ->get()
            ->contains(fn (Theme $theme) => $theme->isAvailableFor(Theme::SCOPE_GLOBAL) || $theme->appliesTo(Theme::SCOPE_GLOBAL));

        if ($isActive && ! $hasReplacement) {
            return back()->with('erro', 'Ative outro tema disponivel antes de arquivar o tema global atual.');
        }

        $manager->archive($tema);

        return back()->with('ok', 'Tema arquivado com sucesso.');
    }

    public function destroy(Theme $tema, ThemeResolver $resolver)
    {
        $activeConsole = $resolver->activeTheme(Theme::SCOPE_CONSOLE);
        $activeSite = $resolver->activeTheme(Theme::SCOPE_SITE);
        $activeAuth = $resolver->activeTheme(Theme::SCOPE_AUTH);

        if ($tema->is_default) {
            return back()->with('erro', 'O tema default não pode ser apagado.');
        }

        if (
            ($activeConsole && $activeConsole->is($tema))
            || ($activeSite && $activeSite->is($tema))
            || ($activeAuth && $activeAuth->is($tema))
        ) {
            return back()->with('erro', 'Desative este tema em todos os escopos antes de apagar.');
        }

        if (Theme::query()->count() <= 1) {
            return back()->with('erro', 'Mantenha pelo menos um tema no sistema.');
        }

        DB::transaction(function () use ($tema) {
            foreach ($tema->persistedAssets() as $path) {
                if (is_string($path) && $path !== '') {
                    $this->deleteAssetIfStored($path);
                }
            }

            if ($tema->preview_image_path) {
                $this->deleteAssetIfStored((string) $tema->preview_image_path);
            }

            $tema->delete();
        });

        $resolver->forgetCache();

        return redirect()
            ->route('admin.temas.index')
            ->with('ok', 'Tema apagado com sucesso.');
    }

    private function validateTheme(Request $request, ?Theme $theme = null): array
    {
        $tokenRules = [];
        foreach (array_keys(Theme::tokenDefinitions()) as $field) {
            $tokenRules["tokens.{$field}"] = ['nullable', 'string', 'max:120'];
        }

        $assetRules = [];
        foreach (Theme::assetKeys() as $field) {
            $assetRules[$field] = ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:6144'];
            $assetRules["remove_{$field}"] = ['nullable', 'boolean'];
        }

        $slug = trim((string) $request->input('slug'));
        $request->merge([
            'slug' => $slug !== '' ? Str::slug($slug) : null,
            'is_default' => $request->boolean('is_default'),
            'remove_preview_image' => $request->boolean('remove_preview_image'),
            'application_scopes' => $request->input('application_scopes', [Theme::SCOPE_GLOBAL]),
        ]);

        $data = $request->validate(array_merge([
            'name' => ['required', 'string', 'max:140'],
            'slug' => [
                'nullable',
                'string',
                'max:160',
                Rule::unique('themes', 'slug')->ignore($theme?->id),
            ],
            'base_theme' => ['required', Rule::in(array_keys($this->baseThemes()))],
            'type' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(Theme::STATUSES)],
            'application_scopes' => ['nullable', 'array'],
            'application_scopes.*' => ['string', Rule::in(Theme::SCOPES)],
            'description' => ['nullable', 'string'],
            'preview_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:6144'],
            'remove_preview_image' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_default' => ['nullable', 'boolean'],
            'config_json' => ['nullable', 'string'],
            'tokens' => ['nullable', 'array'],
        ], $tokenRules, $assetRules), [
            'name.required' => 'Informe o nome do tema.',
            'slug.unique' => 'Ja existe um tema com este slug.',
            'base_theme.required' => 'Selecione a base visual do tema.',
            'application_scopes.*.in' => 'Selecione apenas escopos validos para o tema.',
            'ends_at.after_or_equal' => 'A data final deve ser igual ou posterior a data inicial.',
        ]);

        $configJson = trim((string) ($data['config_json'] ?? ''));
        if ($configJson === '') {
            $data['config_json'] = null;
        } else {
            $decoded = json_decode($configJson, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                throw ValidationException::withMessages([
                    'config_json' => 'Informe um JSON valido para a configuracao avancada do tema.',
                ]);
            }

            $data['config_json'] = $this->sanitizeConfigJson($decoded);
        }

        $data['application_scopes'] = $this->normalizeScopes($data['application_scopes'] ?? null);

        return $data;
    }

    private function persist(Theme $theme, array $data, Request $request): Theme
    {
        $tokens = collect(Theme::tokenDefinitions())
            ->mapWithKeys(function ($label, $key) use ($data, $theme) {
                $submitted = data_get($data, "tokens.{$key}");

                if ($submitted === null) {
                    return [$key => $theme->persistedTokenValue($key)];
                }

                $submitted = trim((string) $submitted);

                return [$key => $submitted !== '' ? $submitted : null];
            })
            ->all();

        $managedAssets = $theme->persistedAssets();

        foreach (Theme::assetKeys() as $field) {
            if ($request->boolean("remove_{$field}") && $theme->hasPersistedAsset($field)) {
                $this->deleteAssetIfStored((string) $theme->persistedAssetPath($field));
                unset($managedAssets[$field]);
            }

            if ($request->hasFile($field)) {
                if ($theme->hasPersistedAsset($field)) {
                    $this->deleteAssetIfStored((string) $theme->persistedAssetPath($field));
                }

                $managedAssets[$field] = $request->file($field)->store("themes/{$field}", 'public');
            }
        }

        $tokens = $theme->mergeManagedTokens($tokens);
        $assets = $theme->mergeManagedAssets($managedAssets);

        if ($request->boolean('remove_preview_image') && $theme->preview_image_path) {
            $this->deleteAssetIfStored($theme->preview_image_path);
            $theme->preview_image_path = null;
        }

        if ($request->hasFile('preview_image')) {
            if ($theme->preview_image_path) {
                $this->deleteAssetIfStored($theme->preview_image_path);
            }

            $theme->preview_image_path = $request->file('preview_image')->store('themes/preview-image', 'public');
        }

        $isCreating = ! $theme->exists;

        $payload = [
            'name' => $data['name'],
            'slug' => Theme::uniqueSlug($data['slug'] ?: $data['name'], $theme->id),
            'base_theme' => $data['base_theme'],
            'type' => $data['type'] ?? null,
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'is_default' => (bool) ($data['is_default'] ?? false),
            'tokens' => $tokens,
            'assets' => $assets,
            'config_json' => $data['config_json'] ?? null,
            'application_scopes' => $data['application_scopes'],
        ];

        if (Schema::hasColumn('themes', 'created_by')) {
            $payload['created_by'] = $isCreating ? auth()->id() : $theme->created_by;
        }

        if (Schema::hasColumn('themes', 'updated_by')) {
            $payload['updated_by'] = auth()->id();
        }

        $theme->fill($payload);

        $theme->save();

        if ($theme->is_default) {
            Theme::query()
                ->whereKeyNot($theme->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        app(ThemeManager::class)->logActivity($theme, $isCreating ? Theme::LOG_ACTION_CREATED : Theme::LOG_ACTION_UPDATED, [
            'status' => $theme->status,
            'scopes' => $theme->normalizedScopes(),
        ]);

        return $theme->refresh();
    }

    private function deleteAssetIfStored(string $path): void
    {
        if (Str::startsWith($path, ['/', 'http://', 'https://', 'imagens/', 'images/'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function validateImportedThemeData(array $themeData): array
    {
        $name = trim((string) ($themeData['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'package' => 'O pacote não informa o nome do tema.',
            ]);
        }

        $baseTheme = (string) ($themeData['base_theme'] ?? '');
        if (! array_key_exists($baseTheme, $this->baseThemes())) {
            throw ValidationException::withMessages([
                'package' => 'O pacote informa um tema base inválido.',
            ]);
        }

        $status = (string) ($themeData['status'] ?? Theme::STATUS_DISPONIVEL);
        if (! in_array($status, Theme::STATUSES, true)) {
            throw ValidationException::withMessages([
                'package' => 'O pacote informa um status inválido.',
            ]);
        }

        $scopes = $this->normalizeScopes((array) ($themeData['application_scopes'] ?? [Theme::SCOPE_GLOBAL]));
        $config = $themeData['config_json'] ?? null;
        if ($config !== null) {
            if (! is_array($config)) {
                throw ValidationException::withMessages([
                    'package' => 'O config_json do pacote deve ser um objeto válido.',
                ]);
            }

            unset($config[Theme::CONFIG_SCOPE_KEY], $config[Theme::LEGACY_CONFIG_SCOPE_KEY]);
            $config = $this->sanitizeConfigJson($config);
        }

        $tokens = collect(Theme::tokenDefinitions())
            ->mapWithKeys(function ($label, $key) use ($themeData) {
                $value = data_get($themeData, "tokens.{$key}");
                $value = is_string($value) ? trim($value) : null;

                return [$key => filled($value) ? Str::limit($value, 120, '') : null];
            })
            ->filter(fn ($value) => $value !== null)
            ->all();

        $assets = [];
        foreach (Theme::assetKeys() as $key) {
            $value = data_get($themeData, "assets.{$key}");
            if (filled($value)) {
                $assets[$key] = (string) $value;
            }
        }

        return [
            'name' => Str::limit($name, 140, ''),
            'slug' => Str::slug((string) ($themeData['slug'] ?? $name)),
            'base_theme' => $baseTheme,
            'type' => filled($themeData['type'] ?? null) ? Str::limit((string) $themeData['type'], 80, '') : null,
            'description' => filled($themeData['description'] ?? null) ? (string) $themeData['description'] : null,
            'status' => $status,
            'application_scopes' => $scopes,
            'starts_at' => filled($themeData['starts_at'] ?? null) ? $themeData['starts_at'] : null,
            'ends_at' => filled($themeData['ends_at'] ?? null) ? $themeData['ends_at'] : null,
            'tokens' => $tokens,
            'assets' => $assets,
            'preview_image' => filled($themeData['preview_image'] ?? null) ? (string) $themeData['preview_image'] : null,
            'config_json' => $config,
        ];
    }

    private function copyAssetToPackage(string $path, string $key, string $tempDir): string
    {
        if (Str::startsWith($path, ['/', 'http://', 'https://', 'imagens/', 'images/'])) {
            return $path;
        }

        $source = storage_path('app/public/' . ltrim($path, '/'));
        if (! FileFacade::exists($source)) {
            return $path;
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION) ?: 'bin';
        $relative = 'assets/' . $key . '.' . $extension;
        $target = $tempDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        FileFacade::ensureDirectoryExists(dirname($target));
        FileFacade::copy($source, $target);

        return $relative;
    }

    private function isPackagedAssetPath(string $path): bool
    {
        return Str::startsWith($path, 'assets/');
    }

    private function storeImportedAsset(string $tempDir, string $relativePath, string $directory, string $slug): string
    {
        $source = $tempDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relativePath, '/'));

        if (! FileFacade::exists($source)) {
            throw ValidationException::withMessages([
                'package' => 'O pacote referencia um asset que não foi encontrado: ' . $relativePath,
            ]);
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION) ?: 'bin';
        $filename = Str::slug($slug ?: 'tema') . '-' . Str::random(8) . '.' . $extension;

        return Storage::disk('public')->putFileAs($directory, new File($source), $filename);
    }

    private function exportableConfig(Theme $theme): ?array
    {
        $config = $theme->resolvedConfig();

        if (! is_array($config) || $config === []) {
            return null;
        }

        unset($config[Theme::CONFIG_SCOPE_KEY], $config[Theme::LEGACY_CONFIG_SCOPE_KEY]);

        return $this->sanitizeConfigJson($config);
    }

    private function baseThemes(): array
    {
        return Theme::baseThemes();
    }

    private function tokenGroups(): array
    {
        $definitions = Theme::tokenDefinitions();

        return [
            [
                'title' => 'Marca e destaque',
                'subtitle' => 'Cores principais, hero e foco institucional.',
                'fields' => $this->buildTokenFields([
                    'ui-primary',
                    'ui-primary-strong',
                    'ui-primary-soft',
                    'ui-focus-ring',
                    'ui-hero-start',
                    'ui-hero-end',
                    'ui-hero-glow',
                ], $definitions),
            ],
            [
                'title' => 'Superficies',
                'subtitle' => 'Fundo global, frame e superficies estruturais.',
                'fields' => $this->buildTokenFields([
                    'ui-app-bg',
                    'ui-app-bg-soft',
                    'ui-frame-bg',
                    'ui-shell-bg',
                    'ui-shell-bg-2',
                    'ui-surface',
                    'ui-surface-soft',
                    'ui-surface-muted',
                    'ui-hover-surface',
                ], $definitions),
            ],
            [
                'title' => 'Texto e leitura',
                'subtitle' => 'Contraste, texto principal e hierarquia visual.',
                'fields' => $this->buildTokenFields([
                    'ui-text',
                    'ui-text-soft',
                    'ui-text-faint',
                    'ui-sidebar-text',
                    'ui-sidebar-text-soft',
                    'ui-sidebar-active-text',
                ], $definitions),
            ],
            [
                'title' => 'Navegacao do console',
                'subtitle' => 'Sidebar, topbar e shell administrativo.',
                'fields' => $this->buildTokenFields([
                    'ui-shell-shadow',
                    'ui-shell-stroke',
                    'ui-sidebar-bg',
                    'ui-sidebar-border',
                    'sidebar_surface',
                    'sidebar_text',
                    'sidebar_section_text',
                    'sidebar_item_bg',
                    'sidebar_item_text',
                    'sidebar_item_icon',
                    'ui-sidebar-hover-bg',
                    'sidebar_item_hover_bg',
                    'sidebar_item_hover_text',
                    'ui-sidebar-active-bg',
                    'sidebar_item_active_bg',
                    'sidebar_item_active_text',
                    'sidebar_item_active_icon',
                    'ui-topbar-bg',
                    'ui-topbar-border',
                ], $definitions),
            ],
            [
                'title' => 'Bordas, sombras e raio',
                'subtitle' => 'Acabamento estrutural de cards e blocos.',
                'fields' => $this->buildTokenFields([
                    'ui-frame-border',
                    'ui-border',
                    'ui-border-strong',
                    'ui-shadow-xs',
                    'ui-shadow-sm',
                    'ui-shadow-md',
                    'ui-shadow-lg',
                    'ui-radius-sm',
                    'ui-radius-md',
                    'ui-radius-lg',
                    'ui-radius-xl',
                ], $definitions),
            ],
            [
                'title' => 'Estados e apoio',
                'subtitle' => 'Feedback visual de sucesso, aviso, perigo e status.',
                'fields' => $this->buildTokenFields([
                    'ui-success',
                    'ui-success-soft',
                    'ui-warning',
                    'ui-warning-soft',
                    'ui-danger',
                    'ui-danger-soft',
                    'ui-state-published-bg',
                    'ui-state-draft-bg',
                    'ui-state-archived-bg',
                    'ui-spacing-card',
                    'ui-spacing-section',
                ], $definitions),
            ],
        ];
    }

    private function assetGroups(): array
    {
        $definitions = Theme::assetDefinitions();

        return [
            [
                'title' => 'Logo e identidade',
                'subtitle' => 'Assets institucionais usados no shell.',
                'fields' => $this->buildAssetFields([
                    'logo',
                ], $definitions),
            ],
            [
                'title' => 'Auth e acesso',
                'subtitle' => 'Background e apoio visual do login.',
                'fields' => $this->buildAssetFields([
                    'login_background',
                ], $definitions),
            ],
            [
                'title' => 'Site e hero',
                'subtitle' => 'Imagem de destaque e apoio visual institucional.',
                'fields' => $this->buildAssetFields([
                    'hero_image',
                ], $definitions),
            ],
        ];
    }

    private function buildTokenFields(array $keys, array $definitions): array
    {
        return collect($keys)
            ->filter(fn (string $key) => array_key_exists($key, $definitions))
            ->map(fn (string $key) => [
                'key' => $key,
                'label' => $definitions[$key],
                'uses_color_picker' => ! $this->isScalarToken($key),
                'placeholder' => $this->isScalarToken($key)
                    ? 'Ex.: 16px, 1rem ou 0 20px 40px rgba(...)'
                    : 'Ex.: #1F6F4A, rgba(...), hsl(...)',
            ])
            ->all();
    }

    private function buildAssetFields(array $keys, array $definitions): array
    {
        return collect($keys)
            ->filter(fn (string $key) => array_key_exists($key, $definitions))
            ->map(fn (string $key) => [
                'key' => $key,
                'label' => $definitions[$key],
            ])
            ->all();
    }

    private function isScalarToken(string $key): bool
    {
        return Str::startsWith($key, ['ui-shadow-', 'ui-radius-', 'ui-spacing-']);
    }

    private function normalizeScopes(?array $scopes): array
    {
        $normalized = collect($scopes ?? [])
            ->map(fn ($scope) => (string) $scope)
            ->filter(fn ($scope) => in_array($scope, Theme::SCOPES, true))
            ->unique()
            ->values()
            ->all();

        return $normalized !== [] ? $normalized : [Theme::SCOPE_GLOBAL];
    }

    private function sanitizeConfigJson(array $decoded): ?array
    {
        $unknownKeys = array_diff(array_keys($decoded), array_keys(Theme::CONFIG_SCHEMA));

        if ($unknownKeys !== []) {
            throw ValidationException::withMessages([
                'config_json' => 'O config_json contem chaves nao suportadas: ' . implode(', ', $unknownKeys) . '.',
            ]);
        }

        $sanitized = [];

        if (isset($decoded['shell'])) {
            if (! is_array($decoded['shell'])) {
                throw ValidationException::withMessages([
                    'config_json' => 'A secao shell do config_json deve ser um objeto JSON.',
                ]);
            }

            $sanitized['shell'] = array_filter([
                'variant' => filled($decoded['shell']['variant'] ?? null) ? Str::limit((string) $decoded['shell']['variant'], 80, '') : null,
                'density' => in_array($decoded['shell']['density'] ?? null, Theme::CONFIG_DENSITIES, true) ? $decoded['shell']['density'] : null,
            ], fn ($value) => $value !== null);
        }

        if (isset($decoded['site'])) {
            if (! is_array($decoded['site'])) {
                throw ValidationException::withMessages([
                    'config_json' => 'A secao site do config_json deve ser um objeto JSON.',
                ]);
            }

            $sanitized['site'] = array_filter([
                'variant' => filled($decoded['site']['variant'] ?? null) ? Str::limit((string) $decoded['site']['variant'], 80, '') : null,
                'hero_variant' => filled($decoded['site']['hero_variant'] ?? null) ? Str::limit((string) $decoded['site']['hero_variant'], 80, '') : null,
            ], fn ($value) => $value !== null);
        }

        if (isset($decoded['auth'])) {
            if (! is_array($decoded['auth'])) {
                throw ValidationException::withMessages([
                    'config_json' => 'A secao auth do config_json deve ser um objeto JSON.',
                ]);
            }

            $sanitized['auth'] = array_filter([
                'variant' => filled($decoded['auth']['variant'] ?? null) ? Str::limit((string) $decoded['auth']['variant'], 80, '') : null,
                'layout' => in_array($decoded['auth']['layout'] ?? null, Theme::CONFIG_AUTH_LAYOUTS, true) ? $decoded['auth']['layout'] : null,
            ], fn ($value) => $value !== null);
        }

        if (isset($decoded['flags'])) {
            if (! is_array($decoded['flags'])) {
                throw ValidationException::withMessages([
                    'config_json' => 'A secao flags do config_json deve ser um objeto JSON.',
                ]);
            }

            $sanitized['flags'] = collect([
                'use_gradient_hero' => $decoded['flags']['use_gradient_hero'] ?? null,
                'use_glass_topbar' => $decoded['flags']['use_glass_topbar'] ?? null,
                'emphasize_brand' => $decoded['flags']['emphasize_brand'] ?? null,
            ])->filter(fn ($value) => is_bool($value))->all();
        }

        if (isset($decoded['notes'])) {
            if (! is_array($decoded['notes'])) {
                throw ValidationException::withMessages([
                    'config_json' => 'A secao notes do config_json deve ser um objeto JSON.',
                ]);
            }

            $sanitized['notes'] = array_filter([
                'internal' => filled($decoded['notes']['internal'] ?? null) ? Str::limit((string) $decoded['notes']['internal'], 500, '') : null,
            ], fn ($value) => $value !== null);
        }

        return $sanitized !== [] ? $sanitized : null;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Services\ThemeManager;
use App\Services\ThemeResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ThemeController extends Controller
{
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
            'previewTheme' => $resolver->previewThemeFor(auth()->user(), Theme::SCOPE_CONSOLE),
        ]);
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
        abort_if($tema->normalizedStatus() === Theme::STATUS_ARQUIVADO, 422, 'Tema arquivado nÃ£o pode ser prÃ©-visualizado.');

        $manager->setPreview($tema);

        return back()->with('ok', 'Preview aplicado apenas na sua sessÃ£o de Admin.');
    }

    public function clearPreview(ThemeManager $manager)
    {
        $manager->clearPreview();

        return back()->with('ok', 'Preview encerrado. O console voltou ao tema global ativo.');
    }

    public function activate(Theme $tema, ThemeManager $manager)
    {
        abort_if($tema->normalizedStatus() === Theme::STATUS_ARQUIVADO, 422, 'Tema arquivado nÃ£o pode ser ativado.');

        if ($tema->normalizedStatus() === Theme::STATUS_RASCUNHO) {
            $updates = [
                'status' => Theme::STATUS_DISPONIVEL,
            ];

            if (Schema::hasColumn('themes', 'updated_by')) {
                $updates['updated_by'] = auth()->id();
            }

            $tema->update($updates);
        }

        $manager->activate($tema);

        return back()->with('ok', 'Tema ativado globalmente no console.');
    }

    public function restoreDefault(ThemeManager $manager)
    {
        $manager->restoreDefault();

        return back()->with('ok', 'Tema padrão restaurado no console.');
    }

    public function archive(Theme $tema, ThemeManager $manager, ThemeResolver $resolver)
    {
        abort_unless(auth()->user()?->can('themes.archive') || auth()->user()?->can('themes.activate'), 403);

        $availableCount = Theme::query()
            ->whereNotIn('status', [Theme::STATUS_ARQUIVADO, 'archived'])
            ->count();

        if ($availableCount <= 1) {
            return back()->with('erro', 'Mantenha pelo menos um tema disponÃ­vel no sistema.');
        }

        $isActive = $resolver->activeTheme(Theme::SCOPE_CONSOLE)?->is($tema);
        $hasReplacement = Theme::query()
            ->whereKeyNot($tema->id)
            ->whereIn('status', [Theme::STATUS_DISPONIVEL, 'available'])
            ->get()
            ->contains(fn (Theme $theme) => $theme->isAvailableFor(Theme::SCOPE_GLOBAL) || $theme->appliesTo(Theme::SCOPE_GLOBAL));

        if ($isActive && ! $hasReplacement) {
            return back()->with('erro', 'Ative outro tema disponÃ­vel antes de arquivar o tema global atual.');
        }

        $manager->archive($tema);

        return back()->with('ok', 'Tema arquivado com sucesso.');
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
            'slug.unique' => 'JÃ¡ existe um tema com este slug.',
            'base_theme.required' => 'Selecione a base visual do tema.',
            'application_scopes.*.in' => 'Selecione apenas escopos vÃ¡lidos para o tema.',
            'ends_at.after_or_equal' => 'A data final deve ser igual ou posterior Ã  data inicial.',
        ]);

        $configJson = trim((string) ($data['config_json'] ?? ''));
        if ($configJson === '') {
            $data['config_json'] = null;
        } else {
            $decoded = json_decode($configJson, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                throw ValidationException::withMessages([
                    'config_json' => 'Informe um JSON vÃ¡lido para a configuraÃ§Ã£o avanÃ§ada do tema.',
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
                'title' => 'SuperfÃ­cies',
                'subtitle' => 'Fundo global, frame e superfÃ­cies estruturais.',
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
                'title' => 'NavegaÃ§Ã£o do console',
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
                'config_json' => 'O config_json contÃ©m chaves nÃ£o suportadas: ' . implode(', ', $unknownKeys) . '.',
            ]);
        }

        $sanitized = [];

        if (isset($decoded['shell'])) {
            if (! is_array($decoded['shell'])) {
                throw ValidationException::withMessages([
                    'config_json' => 'A seÃ§Ã£o shell do config_json deve ser um objeto JSON.',
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
                    'config_json' => 'A seÃ§Ã£o site do config_json deve ser um objeto JSON.',
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
                    'config_json' => 'A seÃ§Ã£o auth do config_json deve ser um objeto JSON.',
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
                    'config_json' => 'A seÃ§Ã£o flags do config_json deve ser um objeto JSON.',
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
                    'config_json' => 'A seÃ§Ã£o notes do config_json deve ser um objeto JSON.',
                ]);
            }

            $sanitized['notes'] = array_filter([
                'internal' => filled($decoded['notes']['internal'] ?? null) ? Str::limit((string) $decoded['notes']['internal'], 500, '') : null,
            ], fn ($value) => $value !== null);
        }

        return $sanitized !== [] ? $sanitized : null;
    }
}


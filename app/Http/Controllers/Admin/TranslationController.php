<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Idioma;
use App\Models\TranslationKey;
use App\Support\TranslationCatalogSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TranslationController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));
        $group = trim((string) $request->input('group', ''));
        $perPageInput = trim((string) $request->input('per_page', '25'));
        $perPageOptions = ['25', '50', '100', 'all'];
        $perPage = in_array($perPageInput, $perPageOptions, true) ? $perPageInput : '25';

        $translationsQuery = TranslationKey::query()
            ->withCount('values')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('key', 'like', "%{$q}%")
                        ->orWhere('group', 'like', "%{$q}%")
                        ->orWhere('base_text', 'like', "%{$q}%");
                });
            })
            ->when($group !== '', fn ($query) => $query->where('group', $group))
            ->orderBy('group')
            ->orderBy('key');

        $totalTranslations = (clone $translationsQuery)->count();
        $pageSize = $perPage === 'all'
            ? max($totalTranslations, 1)
            : (int) $perPage;

        $translations = $translationsQuery
            ->paginate($pageSize)
            ->appends($request->only('q', 'group', 'per_page'));

        $groups = TranslationKey::query()
            ->whereNotNull('group')
            ->where('group', '<>', '')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');

        return view('admin.traducoes.index', compact(
            'translations',
            'q',
            'group',
            'groups',
            'perPage',
            'perPageOptions',
            'totalTranslations'
        ));
    }

    public function sync(TranslationCatalogSync $catalogSync): RedirectResponse
    {
        $count = $catalogSync->syncFromLangFiles();

        return redirect()
            ->route('admin.traducoes.index')
            ->with('ok', $count > 0
                ? "Catálogo sincronizado com sucesso. {$count} chave(s) processada(s)."
                : 'Nenhuma chave foi encontrada para sincronização.');
    }

    public function export(): StreamedResponse
    {
        $idiomas = Idioma::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('nome')
            ->get();

        $translations = TranslationKey::query()
            ->with(['values'])
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        $headers = ['key', 'group', 'description', 'base_text'];
        foreach ($idiomas as $idioma) {
            $headers[] = $idioma->codigo;
        }

        $callback = function () use ($headers, $translations, $idiomas) {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, $headers);

            foreach ($translations as $translation) {
                $row = [
                    $translation->key,
                    $translation->group,
                    $translation->description,
                    $translation->base_text,
                ];

                $valuesByIdioma = $translation->values->pluck('text', 'idioma_id');
                foreach ($idiomas as $idioma) {
                    $row[] = $valuesByIdioma[$idioma->id] ?? '';
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, 'traducoes.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'arquivo' => ['required', 'file', 'mimes:csv,txt'],
        ], [
            'required' => 'Selecione um arquivo CSV para importar.',
            'file' => 'Envie um arquivo válido.',
            'mimes' => 'Use um arquivo CSV.',
        ]);

        $path = $request->file('arquivo')->getRealPath();
        $rows = array_map('str_getcsv', file($path));

        if (count($rows) < 2) {
            return back()->with('erro', 'O arquivo CSV está vazio ou não possui linhas de dados.');
        }

        $header = array_map(fn ($value) => trim((string) $value), array_shift($rows));
        $requiredColumns = ['key', 'group', 'description', 'base_text'];

        foreach ($requiredColumns as $column) {
            if (! in_array($column, $header, true)) {
                return back()->with('erro', "A coluna obrigatória {$column} não foi encontrada no CSV.");
            }
        }

        $idiomas = Idioma::query()
            ->where('is_active', true)
            ->get()
            ->keyBy('codigo');

        $languageColumns = array_values(array_filter($header, fn ($column) => $idiomas->has($column)));
        $indexes = array_flip($header);

        DB::transaction(function () use ($rows, $indexes, $idiomas, $languageColumns) {
            foreach ($rows as $row) {
                if (! is_array($row) || count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                $key = trim((string) ($row[$indexes['key']] ?? ''));
                if ($key === '') {
                    continue;
                }

                $translation = TranslationKey::query()->firstOrNew(['key' => $key]);
                $translation->group = trim((string) ($row[$indexes['group']] ?? '')) ?: null;
                $translation->description = trim((string) ($row[$indexes['description']] ?? '')) ?: null;
                $translation->base_text = trim((string) ($row[$indexes['base_text']] ?? ''));
                $translation->is_active = true;
                $translation->save();

                $values = [];
                foreach ($languageColumns as $column) {
                    $text = trim((string) ($row[$indexes[$column]] ?? ''));
                    if ($text === '') {
                        continue;
                    }

                    $values[$idiomas[$column]->id] = $text;
                }

                $this->syncValues($translation, $values);
            }
        });

        return redirect()
            ->route('admin.traducoes.index')
            ->with('ok', 'CSV importado com sucesso.');
    }

    public function create(): View
    {
        return view('admin.traducoes.create', [
            'translation' => new TranslationKey(['is_active' => true]),
            'idiomas' => Idioma::query()->where('is_active', true)->orderByDesc('is_default')->orderBy('nome')->get(),
            'translationValues' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        DB::transaction(function () use ($payload) {
            $translation = TranslationKey::create($payload['translation']);
            $this->syncValues($translation, $payload['values']);
        });

        return redirect()
            ->route('admin.traducoes.index')
            ->with('ok', 'Chave de tradução cadastrada com sucesso.');
    }

    public function edit(TranslationKey $translation): View
    {
        $translation->load(['values']);

        return view('admin.traducoes.edit', [
            'translation' => $translation,
            'idiomas' => Idioma::query()->where('is_active', true)->orderByDesc('is_default')->orderBy('nome')->get(),
            'translationValues' => $translation->values->pluck('text', 'idioma_id')->all(),
        ]);
    }

    public function update(Request $request, TranslationKey $translation): RedirectResponse
    {
        $payload = $this->validatePayload($request, $translation);

        DB::transaction(function () use ($payload, $translation) {
            $translation->update($payload['translation']);
            $this->syncValues($translation, $payload['values']);
        });

        return redirect()
            ->route('admin.traducoes.index')
            ->with('ok', 'Chave de tradução atualizada com sucesso.');
    }

    public function destroy(TranslationKey $translation): RedirectResponse
    {
        $translation->delete();

        return redirect()
            ->route('admin.traducoes.index')
            ->with('ok', 'Chave de tradução excluída com sucesso.');
    }

    private function validatePayload(Request $request, ?TranslationKey $translation = null): array
    {
        $request->merge([
            'key' => trim((string) $request->input('key')),
            'group' => trim((string) $request->input('group')) ?: null,
            'description' => trim((string) $request->input('description')) ?: null,
            'base_text' => trim((string) $request->input('base_text')),
            'is_active' => $request->boolean('is_active'),
        ]);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:190', Rule::unique('translation_keys', 'key')->ignore($translation?->id)],
            'group' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'base_text' => ['required', 'string'],
            'is_active' => ['boolean'],
            'values' => ['array'],
            'values.*' => ['nullable', 'string'],
        ], [
            'required' => 'Campo obrigatório.',
            'unique' => 'Já existe uma chave com este valor.',
            'max' => 'O campo :attribute ultrapassa o limite permitido.',
        ], [
            'key' => 'chave',
            'group' => 'grupo',
            'description' => 'descrição',
            'base_text' => 'texto base',
        ]);

        $idiomaIds = Idioma::query()->where('is_active', true)->pluck('id')->all();
        $values = collect((array) ($validated['values'] ?? []))
            ->filter(fn ($text, $idiomaId) => in_array((int) $idiomaId, $idiomaIds, true) && trim((string) $text) !== '')
            ->map(fn ($text) => trim((string) $text))
            ->all();

        return [
            'translation' => [
                'key' => $validated['key'],
                'group' => $validated['group'],
                'description' => $validated['description'],
                'base_text' => $validated['base_text'],
                'is_active' => $validated['is_active'],
            ],
            'values' => $values,
        ];
    }

    private function syncValues(TranslationKey $translation, array $values): void
    {
        $translation->values()->delete();

        foreach ($values as $idiomaId => $text) {
            $translation->values()->create([
                'idioma_id' => (int) $idiomaId,
                'text' => $text,
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Idioma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IdiomaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));

        $idiomas = Idioma::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('nome', 'like', "%{$q}%")
                        ->orWhere('sigla', 'like', "%{$q}%")
                        ->orWhere('codigo', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('is_default')
            ->orderBy('nome')
            ->paginate(12)
            ->appends($request->only('q'));

        return view('admin.idiomas.index', compact('idiomas', 'q'));
    }

    public function create(): View
    {
        return view('admin.idiomas.create', [
            'idioma' => new Idioma([
                'is_active' => true,
                'is_default' => false,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);

        $this->normalizeDefaultFlags($data);

        Idioma::create($data);

        return redirect()
            ->route('admin.idiomas.index')
            ->with('ok', 'Idioma cadastrado com sucesso.');
    }

    public function edit(Idioma $idioma): View
    {
        return view('admin.idiomas.edit', compact('idioma'));
    }

    public function update(Request $request, Idioma $idioma): RedirectResponse
    {
        $data = $this->validatePayload($request, $idioma);

        $this->normalizeDefaultFlags($data, $idioma);

        $idioma->update($data);

        return redirect()
            ->route('admin.idiomas.index')
            ->with('ok', 'Idioma atualizado com sucesso.');
    }

    public function destroy(Idioma $idioma): RedirectResponse
    {
        if ($idioma->is_default) {
            return back()->with('erro', 'O idioma padrão não pode ser excluído.');
        }

        $idioma->delete();

        return redirect()
            ->route('admin.idiomas.index')
            ->with('ok', 'Idioma excluído com sucesso.');
    }

    private function validatePayload(Request $request, ?Idioma $idioma = null): array
    {
        $request->merge([
            'codigo' => strtolower(trim((string) $request->input('codigo'))),
            'sigla' => strtoupper(trim((string) $request->input('sigla'))),
            'nome' => trim((string) $request->input('nome')),
            'bandeira' => trim((string) $request->input('bandeira')) ?: null,
            'html_lang' => trim((string) $request->input('html_lang')) ?: null,
            'hreflang' => trim((string) $request->input('hreflang')) ?: null,
            'og_locale' => trim((string) $request->input('og_locale')) ?: null,
            'is_active' => $request->boolean('is_active'),
            'is_default' => $request->boolean('is_default'),
        ]);

        return $request->validate([
            'codigo' => ['required', 'string', 'max:12', 'regex:/^[a-z]{2,10}([_-][a-z0-9]{2,10})?$/', Rule::unique('idiomas', 'codigo')->ignore($idioma?->id)],
            'nome' => ['required', 'string', 'max:120'],
            'sigla' => ['required', 'string', 'max:12'],
            'bandeira' => ['nullable', 'string', 'max:255'],
            'html_lang' => ['nullable', 'string', 'max:20'],
            'hreflang' => ['nullable', 'string', 'max:20'],
            'og_locale' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
        ], [
            'required' => 'Campo obrigatório.',
            'max' => 'O campo :attribute ultrapassa o limite permitido.',
            'regex' => 'Informe um código válido, como pt, en ou es.',
            'unique' => 'Já existe um idioma com este :attribute.',
        ], [
            'codigo' => 'código',
            'nome' => 'nome',
            'sigla' => 'sigla',
            'bandeira' => 'bandeira',
            'html_lang' => 'HTML lang',
            'hreflang' => 'hreflang',
            'og_locale' => 'OG locale',
        ]);
    }

    private function normalizeDefaultFlags(array &$data, ?Idioma $idioma = null): void
    {
        if ($data['is_default']) {
            Idioma::query()
                ->when($idioma, fn ($query) => $query->whereKeyNot($idioma->getKey()))
                ->update(['is_default' => false]);

            $data['is_active'] = true;

            return;
        }

        if ($idioma?->is_default) {
            $data['is_default'] = true;
            $data['is_active'] = true;
        }
    }
}

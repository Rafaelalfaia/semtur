<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\Empresa;
use App\Models\Conteudo\OndeFicarPagina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OndeFicarController extends Controller
{
    public function edit()
    {
        $pagina = OndeFicarPagina::singleton();

        $empresas = Empresa::query()
            ->publicadas()
            ->orderBy('nome')
            ->get(['id', 'nome', 'cidade']);

        return view('coordenador.onde-ficar.edit', [
            'pagina' => $pagina,
            'empresas' => $empresas,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:180'],
            'subtitulo' => ['nullable', 'string', 'max:180'],
            'resumo' => ['required', 'string', 'max:1500'],

            'texto_intro' => ['nullable', 'string'],
            'texto_hospedagem_local' => ['nullable', 'string'],
            'texto_dicas' => ['nullable', 'string'],

            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:255'],

            'status' => ['required', Rule::in(OndeFicarPagina::STATUS)],

            'hero' => ['nullable', 'image', 'max:6144'],
            'remover_hero' => ['nullable', 'boolean'],

            'empresas' => ['nullable', 'array'],
            'empresas.*.empresa_id' => ['required', 'integer', 'distinct', 'exists:empresas,id'],
            'empresas.*.observacao_curta' => ['nullable', 'string', 'max:255'],
            'empresas.*.destaque' => ['nullable', 'boolean'],
        ]);

        if (($data['status'] ?? null) === OndeFicarPagina::STATUS_PUBLICADO && empty($data['empresas'])) {
            return back()
                ->withErrors(['empresas' => 'Selecione ao menos uma empresa para publicar a página.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $data) {
            $pagina = OndeFicarPagina::query()->first() ?? new OndeFicarPagina();

            $pagina->fill([
                'titulo' => $data['titulo'],
                'subtitulo' => $this->nullable($data['subtitulo'] ?? null),
                'resumo' => $data['resumo'],
                'texto_intro' => $this->nullable($data['texto_intro'] ?? null),
                'texto_hospedagem_local' => $this->nullable($data['texto_hospedagem_local'] ?? null),
                'texto_dicas' => $this->nullable($data['texto_dicas'] ?? null),
                'seo_title' => $this->nullable($data['seo_title'] ?? null),
                'seo_description' => $this->nullable($data['seo_description'] ?? null),
                'status' => $data['status'],
            ]);

            if (!$pagina->exists) {
                $pagina->created_by = auth()->id();
            }

            $pagina->updated_by = auth()->id();

            if ($request->boolean('remover_hero') && $pagina->hero_path) {
                Storage::disk('public')->delete($pagina->hero_path);
                $pagina->hero_path = null;
            }

            if ($request->hasFile('hero')) {
                if ($pagina->hero_path) {
                    Storage::disk('public')->delete($pagina->hero_path);
                }

                $pagina->hero_path = ltrim(
                    $request->file('hero')->store('onde-ficar', 'public'),
                    '/'
                );
            }

            $pagina->save();

            $pagina->empresasSelecionadas()->delete();

            foreach (array_values($data['empresas'] ?? []) as $index => $empresaData) {
                $pagina->empresasSelecionadas()->create([
                    'empresa_id' => (int) $empresaData['empresa_id'],
                    'ordem' => $index,
                    'destaque' => (bool) ($empresaData['destaque'] ?? false),
                    'observacao_curta' => $this->nullable($empresaData['observacao_curta'] ?? null),
                ]);
            }
        });

        return back()->with('ok', 'Página “Onde ficar” atualizada com sucesso.');
    }

    private function nullable(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        return $value === '' ? null : $value;
    }
}

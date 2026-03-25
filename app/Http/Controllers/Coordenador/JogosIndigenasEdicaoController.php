<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveJogosIndigenasEdicaoRequest;
use App\Models\JogosIndigenas;
use App\Models\JogosIndigenasEdicao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JogosIndigenasEdicaoController extends Controller
{
    public function index(JogosIndigenas $jogosIndigena)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $edicoes = $jogosIndigena->edicoes()
            ->withCount(['fotos', 'videos', 'patrocinadores'])
            ->paginate(20)
            ->withQueryString();

        return view('coordenador.jogos-indigenas.edicoes.index', [
            'jogo' => $jogosIndigena,
            'edicoes' => $edicoes,
            'statuses' => JogosIndigenasEdicao::STATUS,
        ]);
    }

    public function create(JogosIndigenas $jogosIndigena)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        return view('coordenador.jogos-indigenas.edicoes.create', [
            'jogo' => $jogosIndigena,
            'edicao' => new JogosIndigenasEdicao([
                'ano' => now()->year,
                'status' => JogosIndigenasEdicao::STATUS_RASCUNHO,
                'ordem' => 0,
            ]),
            'statuses' => JogosIndigenasEdicao::STATUS,
        ]);
    }

    public function store(SaveJogosIndigenasEdicaoRequest $request, JogosIndigenas $jogosIndigena)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $edicao = DB::transaction(function () use ($request, $jogosIndigena) {
            return $this->persist(new JogosIndigenasEdicao(), $request, $jogosIndigena);
        });

        return redirect()
            ->route('coordenador.jogos-indigenas.edicoes.edit', [$jogosIndigena, $edicao])
            ->with('ok', 'Edicao criada com sucesso.');
    }

    public function edit(JogosIndigenas $jogosIndigena, JogosIndigenasEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        abort_unless($edicao->jogos_indigenas_id === $jogosIndigena->id, 404);

        return view('coordenador.jogos-indigenas.edicoes.edit', [
            'jogo' => $jogosIndigena,
            'edicao' => $edicao->loadCount(['fotos', 'videos', 'patrocinadores']),
            'statuses' => JogosIndigenasEdicao::STATUS,
        ]);
    }

    public function update(
        SaveJogosIndigenasEdicaoRequest $request,
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        abort_unless($edicao->jogos_indigenas_id === $jogosIndigena->id, 404);

        DB::transaction(function () use ($request, $edicao, $jogosIndigena) {
            $this->persist($edicao, $request, $jogosIndigena);
        });

        return back()->with('ok', 'Edicao atualizada com sucesso.');
    }

    public function destroy(JogosIndigenas $jogosIndigena, JogosIndigenasEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        abort_unless($edicao->jogos_indigenas_id === $jogosIndigena->id, 404);

        DB::transaction(function () use ($edicao) {
            if ($edicao->capa_path) {
                Storage::disk('public')->delete($edicao->capa_path);
            }

            $edicao->fotos()->each(function ($foto) {
                if ($foto->imagem_path) {
                    Storage::disk('public')->delete($foto->imagem_path);
                }

                $foto->delete();
            });

            $edicao->patrocinadores()->each(function ($patrocinador) {
                if ($patrocinador->logo_path) {
                    Storage::disk('public')->delete($patrocinador->logo_path);
                }

                $patrocinador->delete();
            });

            $edicao->videos()->each->delete();
            $edicao->delete();
        });

        return redirect()
            ->route('coordenador.jogos-indigenas.edicoes.index', $jogosIndigena)
            ->with('ok', 'Edicao movida para a lixeira.');
    }

    private function principal(): ?JogosIndigenas
    {
        return JogosIndigenas::query()->orderBy('id')->first();
    }

    private function redirectIfNotPrincipal(JogosIndigenas $jogo)
    {
        $principal = $this->principal();

        if (!$principal || $principal->is($jogo)) {
            return null;
        }

        return redirect()
            ->route('coordenador.jogos-indigenas.edicoes.index', $principal)
            ->with('erro', 'As edicoes devem ser gerenciadas a partir do cadastro principal de Jogos Indigenas.');
    }

    private function persist(
        JogosIndigenasEdicao $edicao,
        SaveJogosIndigenasEdicaoRequest $request,
        JogosIndigenas $jogo
    ): JogosIndigenasEdicao {
        $data = $request->validated();

        $edicao->fill([
            'jogos_indigenas_id' => $jogo->id,
            'ano' => $data['ano'],
            'titulo' => $data['titulo'],
            'slug' => $data['slug'] ?? null,
            'descricao' => $data['descricao'],
            'ordem' => $data['ordem'] ?? 0,
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? null,
        ]);

        if ($request->boolean('remover_capa') && $edicao->capa_path) {
            Storage::disk('public')->delete($edicao->capa_path);
            $edicao->capa_path = null;
        }

        if ($request->hasFile('capa')) {
            if ($edicao->capa_path) {
                Storage::disk('public')->delete($edicao->capa_path);
            }

            $edicao->capa_path = $request->file('capa')->store('jogos-indigenas/edicoes/capas', 'public');
        }

        $edicao->save();

        return $edicao;
    }
}

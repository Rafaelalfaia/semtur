<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveJogosIndigenasEdicaoFotoRequest;
use App\Models\JogosIndigenas;
use App\Models\JogosIndigenasEdicao;
use App\Models\JogosIndigenasEdicaoFoto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JogosIndigenasEdicaoFotoController extends Controller
{
    public function index(JogosIndigenas $jogosIndigena, JogosIndigenasEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);

        return view('coordenador.jogos-indigenas.edicoes.fotos.index', [
            'jogo' => $jogosIndigena,
            'edicao' => $edicao,
            'fotos' => $edicao->fotos()->paginate(24)->withQueryString(),
        ]);
    }

    public function create(JogosIndigenas $jogosIndigena, JogosIndigenasEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);

        return view('coordenador.jogos-indigenas.edicoes.fotos.create', [
            'jogo' => $jogosIndigena,
            'edicao' => $edicao,
            'foto' => new JogosIndigenasEdicaoFoto(['ordem' => 0]),
        ]);
    }

    public function store(
        SaveJogosIndigenasEdicaoFotoRequest $request,
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);

        DB::transaction(function () use ($request, $edicao) {
            $this->persist(new JogosIndigenasEdicaoFoto(), $request, $edicao);
        });

        return redirect()
            ->route('coordenador.jogos-indigenas.edicoes.fotos.index', [$jogosIndigena, $edicao])
            ->with('ok', 'Foto cadastrada com sucesso.');
    }

    public function edit(
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao,
        JogosIndigenasEdicaoFoto $foto
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);
        abort_unless($foto->jogos_indigenas_edicao_id === $edicao->id, 404);

        return view('coordenador.jogos-indigenas.edicoes.fotos.edit', [
            'jogo' => $jogosIndigena,
            'edicao' => $edicao,
            'foto' => $foto,
        ]);
    }

    public function update(
        SaveJogosIndigenasEdicaoFotoRequest $request,
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao,
        JogosIndigenasEdicaoFoto $foto
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);
        abort_unless($foto->jogos_indigenas_edicao_id === $edicao->id, 404);

        DB::transaction(function () use ($request, $edicao, $foto) {
            $this->persist($foto, $request, $edicao);
        });

        return redirect()
            ->route('coordenador.jogos-indigenas.edicoes.fotos.index', [$jogosIndigena, $edicao])
            ->with('ok', 'Foto atualizada com sucesso.');
    }

    public function destroy(
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao,
        JogosIndigenasEdicaoFoto $foto
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);
        abort_unless($foto->jogos_indigenas_edicao_id === $edicao->id, 404);

        DB::transaction(function () use ($foto) {
            if ($foto->imagem_path) {
                Storage::disk('public')->delete($foto->imagem_path);
            }

            $foto->delete();
        });

        return redirect()
            ->route('coordenador.jogos-indigenas.edicoes.fotos.index', [$jogosIndigena, $edicao])
            ->with('ok', 'Foto removida com sucesso.');
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
            ->with('erro', 'As fotos devem ser gerenciadas a partir do cadastro principal de Jogos Indigenas.');
    }

    private function ensureEdicaoBelongsToJogo(JogosIndigenas $jogo, JogosIndigenasEdicao $edicao): void
    {
        abort_unless($edicao->jogos_indigenas_id === $jogo->id, 404);
    }

    private function persist(
        JogosIndigenasEdicaoFoto $foto,
        SaveJogosIndigenasEdicaoFotoRequest $request,
        JogosIndigenasEdicao $edicao
    ): JogosIndigenasEdicaoFoto {
        $data = $request->validated();

        $foto->fill([
            'jogos_indigenas_edicao_id' => $edicao->id,
            'legenda' => $data['legenda'] ?? null,
            'ordem' => $data['ordem'] ?? 0,
        ]);

        if ($request->hasFile('imagem')) {
            if ($foto->imagem_path) {
                Storage::disk('public')->delete($foto->imagem_path);
            }

            $foto->imagem_path = $request->file('imagem')->store('jogos-indigenas/edicoes/fotos', 'public');
        }

        $foto->save();

        return $foto;
    }
}

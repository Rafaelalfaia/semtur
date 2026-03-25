<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveJogosIndigenasEdicaoVideoRequest;
use App\Models\JogosIndigenas;
use App\Models\JogosIndigenasEdicao;
use App\Models\JogosIndigenasEdicaoVideo;
use Illuminate\Support\Facades\DB;

class JogosIndigenasEdicaoVideoController extends Controller
{
    public function index(JogosIndigenas $jogosIndigena, JogosIndigenasEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);

        return view('coordenador.jogos-indigenas.edicoes.videos.index', [
            'jogo' => $jogosIndigena,
            'edicao' => $edicao,
            'videos' => $edicao->videos()->paginate(20)->withQueryString(),
        ]);
    }

    public function create(JogosIndigenas $jogosIndigena, JogosIndigenasEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);

        return view('coordenador.jogos-indigenas.edicoes.videos.create', [
            'jogo' => $jogosIndigena,
            'edicao' => $edicao,
            'video' => new JogosIndigenasEdicaoVideo(['ordem' => 0]),
        ]);
    }

    public function store(
        SaveJogosIndigenasEdicaoVideoRequest $request,
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);

        DB::transaction(function () use ($request, $edicao) {
            $this->persist(new JogosIndigenasEdicaoVideo(), $request, $edicao);
        });

        return redirect()
            ->route('coordenador.jogos-indigenas.edicoes.videos.index', [$jogosIndigena, $edicao])
            ->with('ok', 'Video cadastrado com sucesso.');
    }

    public function edit(
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao,
        JogosIndigenasEdicaoVideo $video
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);
        abort_unless($video->jogos_indigenas_edicao_id === $edicao->id, 404);

        return view('coordenador.jogos-indigenas.edicoes.videos.edit', [
            'jogo' => $jogosIndigena,
            'edicao' => $edicao,
            'video' => $video,
        ]);
    }

    public function update(
        SaveJogosIndigenasEdicaoVideoRequest $request,
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao,
        JogosIndigenasEdicaoVideo $video
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);
        abort_unless($video->jogos_indigenas_edicao_id === $edicao->id, 404);

        DB::transaction(function () use ($request, $edicao, $video) {
            $this->persist($video, $request, $edicao);
        });

        return redirect()
            ->route('coordenador.jogos-indigenas.edicoes.videos.index', [$jogosIndigena, $edicao])
            ->with('ok', 'Video atualizado com sucesso.');
    }

    public function destroy(
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao,
        JogosIndigenasEdicaoVideo $video
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);
        abort_unless($video->jogos_indigenas_edicao_id === $edicao->id, 404);

        $video->delete();

        return redirect()
            ->route('coordenador.jogos-indigenas.edicoes.videos.index', [$jogosIndigena, $edicao])
            ->with('ok', 'Video removido com sucesso.');
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
            ->with('erro', 'Os videos devem ser gerenciados a partir do cadastro principal de Jogos Indigenas.');
    }

    private function ensureEdicaoBelongsToJogo(JogosIndigenas $jogo, JogosIndigenasEdicao $edicao): void
    {
        abort_unless($edicao->jogos_indigenas_id === $jogo->id, 404);
    }

    private function persist(
        JogosIndigenasEdicaoVideo $video,
        SaveJogosIndigenasEdicaoVideoRequest $request,
        JogosIndigenasEdicao $edicao
    ): JogosIndigenasEdicaoVideo {
        $data = $request->validated();

        $video->fill([
            'jogos_indigenas_edicao_id' => $edicao->id,
            'titulo' => $data['titulo'],
            'descricao' => $data['descricao'] ?? null,
            'drive_url' => $data['drive_url'],
            'embed_url' => $data['embed_url'] ?? null,
            'ordem' => $data['ordem'] ?? 0,
        ]);

        $video->save();

        return $video;
    }
}

<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRotaDoCacauEdicaoVideoRequest;
use App\Models\RotaDoCacau;
use App\Models\RotaDoCacauEdicao;
use App\Models\RotaDoCacauEdicaoVideo;
use Illuminate\Support\Facades\DB;

class RotaDoCacauEdicaoVideoController extends Controller
{
    public function index(RotaDoCacau $rotaDoCacau, RotaDoCacauEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);

        return view('coordenador.rota-do-cacau.edicoes.videos.index', [
            'rota' => $rotaDoCacau,
            'edicao' => $edicao,
            'videos' => $edicao->videos()->paginate(20)->withQueryString(),
        ]);
    }

    public function create(RotaDoCacau $rotaDoCacau, RotaDoCacauEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);

        return view('coordenador.rota-do-cacau.edicoes.videos.create', [
            'rota' => $rotaDoCacau,
            'edicao' => $edicao,
            'video' => new RotaDoCacauEdicaoVideo(['ordem' => 0]),
        ]);
    }

    public function store(
        SaveRotaDoCacauEdicaoVideoRequest $request,
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);

        DB::transaction(function () use ($request, $edicao) {
            $this->persist(new RotaDoCacauEdicaoVideo(), $request, $edicao);
        });

        return redirect()
            ->route('coordenador.rota-do-cacau.edicoes.videos.index', [$rotaDoCacau, $edicao])
            ->with('ok', 'Video cadastrado com sucesso.');
    }

    public function edit(
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao,
        RotaDoCacauEdicaoVideo $video
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);
        abort_unless($video->rota_do_cacau_edicao_id === $edicao->id, 404);

        return view('coordenador.rota-do-cacau.edicoes.videos.edit', [
            'rota' => $rotaDoCacau,
            'edicao' => $edicao,
            'video' => $video,
        ]);
    }

    public function update(
        SaveRotaDoCacauEdicaoVideoRequest $request,
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao,
        RotaDoCacauEdicaoVideo $video
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);
        abort_unless($video->rota_do_cacau_edicao_id === $edicao->id, 404);

        DB::transaction(function () use ($request, $edicao, $video) {
            $this->persist($video, $request, $edicao);
        });

        return redirect()
            ->route('coordenador.rota-do-cacau.edicoes.videos.index', [$rotaDoCacau, $edicao])
            ->with('ok', 'Video atualizado com sucesso.');
    }

    public function destroy(
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao,
        RotaDoCacauEdicaoVideo $video
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);
        abort_unless($video->rota_do_cacau_edicao_id === $edicao->id, 404);

        $video->delete();

        return redirect()
            ->route('coordenador.rota-do-cacau.edicoes.videos.index', [$rotaDoCacau, $edicao])
            ->with('ok', 'Video removido com sucesso.');
    }

    private function principal(): ?RotaDoCacau
    {
        return RotaDoCacau::query()->orderBy('id')->first();
    }

    private function redirectIfNotPrincipal(RotaDoCacau $rota): mixed
    {
        $principal = $this->principal();

        if (!$principal || $principal->is($rota)) {
            return null;
        }

        return redirect()
            ->route('coordenador.rota-do-cacau.edicoes.index', $principal)
            ->with('erro', 'Os videos devem ser gerenciados a partir do cadastro principal de Rota do Cacau.');
    }

    private function ensureEdicaoBelongsToRota(RotaDoCacau $rota, RotaDoCacauEdicao $edicao): void
    {
        abort_unless($edicao->rota_do_cacau_id === $rota->id, 404);
    }

    private function persist(
        RotaDoCacauEdicaoVideo $video,
        SaveRotaDoCacauEdicaoVideoRequest $request,
        RotaDoCacauEdicao $edicao
    ): RotaDoCacauEdicaoVideo {
        $data = $request->validated();

        $video->fill([
            'rota_do_cacau_edicao_id' => $edicao->id,
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

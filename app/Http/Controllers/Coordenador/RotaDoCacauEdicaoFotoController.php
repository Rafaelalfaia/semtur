<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRotaDoCacauEdicaoFotoRequest;
use App\Models\RotaDoCacau;
use App\Models\RotaDoCacauEdicao;
use App\Models\RotaDoCacauEdicaoFoto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RotaDoCacauEdicaoFotoController extends Controller
{
    public function index(RotaDoCacau $rotaDoCacau, RotaDoCacauEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);

        return view('coordenador.rota-do-cacau.edicoes.fotos.index', [
            'rota' => $rotaDoCacau,
            'edicao' => $edicao,
            'fotos' => $edicao->fotos()->paginate(24)->withQueryString(),
        ]);
    }

    public function create(RotaDoCacau $rotaDoCacau, RotaDoCacauEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);

        return view('coordenador.rota-do-cacau.edicoes.fotos.create', [
            'rota' => $rotaDoCacau,
            'edicao' => $edicao,
            'foto' => new RotaDoCacauEdicaoFoto(['ordem' => 0]),
        ]);
    }

    public function store(
        SaveRotaDoCacauEdicaoFotoRequest $request,
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);

        DB::transaction(function () use ($request, $edicao) {
            $this->persist(new RotaDoCacauEdicaoFoto(), $request, $edicao);
        });

        return redirect()
            ->route('coordenador.rota-do-cacau.edicoes.fotos.index', [$rotaDoCacau, $edicao])
            ->with('ok', 'Foto cadastrada com sucesso.');
    }

    public function edit(
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao,
        RotaDoCacauEdicaoFoto $foto
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);
        abort_unless($foto->rota_do_cacau_edicao_id === $edicao->id, 404);

        return view('coordenador.rota-do-cacau.edicoes.fotos.edit', [
            'rota' => $rotaDoCacau,
            'edicao' => $edicao,
            'foto' => $foto,
        ]);
    }

    public function update(
        SaveRotaDoCacauEdicaoFotoRequest $request,
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao,
        RotaDoCacauEdicaoFoto $foto
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);
        abort_unless($foto->rota_do_cacau_edicao_id === $edicao->id, 404);

        DB::transaction(function () use ($request, $edicao, $foto) {
            $this->persist($foto, $request, $edicao);
        });

        return redirect()
            ->route('coordenador.rota-do-cacau.edicoes.fotos.index', [$rotaDoCacau, $edicao])
            ->with('ok', 'Foto atualizada com sucesso.');
    }

    public function destroy(
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao,
        RotaDoCacauEdicaoFoto $foto
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);
        abort_unless($foto->rota_do_cacau_edicao_id === $edicao->id, 404);

        DB::transaction(function () use ($foto) {
            if ($foto->imagem_path) {
                Storage::disk('public')->delete($foto->imagem_path);
            }

            $foto->delete();
        });

        return redirect()
            ->route('coordenador.rota-do-cacau.edicoes.fotos.index', [$rotaDoCacau, $edicao])
            ->with('ok', 'Foto removida com sucesso.');
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
            ->with('erro', 'As fotos devem ser gerenciadas a partir do cadastro principal de Rota do Cacau.');
    }

    private function ensureEdicaoBelongsToRota(RotaDoCacau $rota, RotaDoCacauEdicao $edicao): void
    {
        abort_unless($edicao->rota_do_cacau_id === $rota->id, 404);
    }

    private function persist(
        RotaDoCacauEdicaoFoto $foto,
        SaveRotaDoCacauEdicaoFotoRequest $request,
        RotaDoCacauEdicao $edicao
    ): RotaDoCacauEdicaoFoto {
        $data = $request->validated();

        $foto->fill([
            'rota_do_cacau_edicao_id' => $edicao->id,
            'legenda' => $data['legenda'] ?? null,
            'ordem' => $data['ordem'] ?? 0,
        ]);

        if ($request->hasFile('imagem')) {
            if ($foto->imagem_path) {
                Storage::disk('public')->delete($foto->imagem_path);
            }

            $foto->imagem_path = $request->file('imagem')->store('rota-do-cacau/edicoes/fotos', 'public');
        }

        $foto->save();

        return $foto;
    }
}

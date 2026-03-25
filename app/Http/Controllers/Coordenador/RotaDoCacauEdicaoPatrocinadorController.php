<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRotaDoCacauEdicaoPatrocinadorRequest;
use App\Models\RotaDoCacau;
use App\Models\RotaDoCacauEdicao;
use App\Models\RotaDoCacauEdicaoPatrocinador;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RotaDoCacauEdicaoPatrocinadorController extends Controller
{
    public function index(RotaDoCacau $rotaDoCacau, RotaDoCacauEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);

        return view('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [
            'rota' => $rotaDoCacau,
            'edicao' => $edicao,
            'patrocinadores' => $edicao->patrocinadores()->paginate(20)->withQueryString(),
        ]);
    }

    public function create(RotaDoCacau $rotaDoCacau, RotaDoCacauEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);

        return view('coordenador.rota-do-cacau.edicoes.patrocinadores.create', [
            'rota' => $rotaDoCacau,
            'edicao' => $edicao,
            'patrocinador' => new RotaDoCacauEdicaoPatrocinador(['ordem' => 0]),
        ]);
    }

    public function store(
        SaveRotaDoCacauEdicaoPatrocinadorRequest $request,
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);

        DB::transaction(function () use ($request, $edicao) {
            $this->persist(new RotaDoCacauEdicaoPatrocinador(), $request, $edicao);
        });

        return redirect()
            ->route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rotaDoCacau, $edicao])
            ->with('ok', 'Patrocinador cadastrado com sucesso.');
    }

    public function edit(
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao,
        RotaDoCacauEdicaoPatrocinador $patrocinador
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);
        abort_unless($patrocinador->rota_do_cacau_edicao_id === $edicao->id, 404);

        return view('coordenador.rota-do-cacau.edicoes.patrocinadores.edit', [
            'rota' => $rotaDoCacau,
            'edicao' => $edicao,
            'patrocinador' => $patrocinador,
        ]);
    }

    public function update(
        SaveRotaDoCacauEdicaoPatrocinadorRequest $request,
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao,
        RotaDoCacauEdicaoPatrocinador $patrocinador
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);
        abort_unless($patrocinador->rota_do_cacau_edicao_id === $edicao->id, 404);

        DB::transaction(function () use ($request, $edicao, $patrocinador) {
            $this->persist($patrocinador, $request, $edicao);
        });

        return redirect()
            ->route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rotaDoCacau, $edicao])
            ->with('ok', 'Patrocinador atualizado com sucesso.');
    }

    public function destroy(
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao,
        RotaDoCacauEdicaoPatrocinador $patrocinador
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToRota($rotaDoCacau, $edicao);
        abort_unless($patrocinador->rota_do_cacau_edicao_id === $edicao->id, 404);

        DB::transaction(function () use ($patrocinador) {
            if ($patrocinador->logo_path) {
                Storage::disk('public')->delete($patrocinador->logo_path);
            }

            $patrocinador->delete();
        });

        return redirect()
            ->route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rotaDoCacau, $edicao])
            ->with('ok', 'Patrocinador removido com sucesso.');
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
            ->with('erro', 'Os patrocinadores devem ser gerenciados a partir do cadastro principal de Rota do Cacau.');
    }

    private function ensureEdicaoBelongsToRota(RotaDoCacau $rota, RotaDoCacauEdicao $edicao): void
    {
        abort_unless($edicao->rota_do_cacau_id === $rota->id, 404);
    }

    private function persist(
        RotaDoCacauEdicaoPatrocinador $patrocinador,
        SaveRotaDoCacauEdicaoPatrocinadorRequest $request,
        RotaDoCacauEdicao $edicao
    ): RotaDoCacauEdicaoPatrocinador {
        $data = $request->validated();

        $patrocinador->fill([
            'rota_do_cacau_edicao_id' => $edicao->id,
            'nome' => $data['nome'],
            'url' => $data['url'] ?? null,
            'ordem' => $data['ordem'] ?? 0,
        ]);

        if ($request->boolean('remover_logo') && $patrocinador->logo_path) {
            Storage::disk('public')->delete($patrocinador->logo_path);
            $patrocinador->logo_path = null;
        }

        if ($request->hasFile('logo')) {
            if ($patrocinador->logo_path) {
                Storage::disk('public')->delete($patrocinador->logo_path);
            }

            $patrocinador->logo_path = $request->file('logo')->store('rota-do-cacau/edicoes/patrocinadores', 'public');
        }

        $patrocinador->save();

        return $patrocinador;
    }
}

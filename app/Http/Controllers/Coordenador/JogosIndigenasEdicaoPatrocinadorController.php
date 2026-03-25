<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveJogosIndigenasEdicaoPatrocinadorRequest;
use App\Models\JogosIndigenas;
use App\Models\JogosIndigenasEdicao;
use App\Models\JogosIndigenasEdicaoPatrocinador;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JogosIndigenasEdicaoPatrocinadorController extends Controller
{
    public function index(JogosIndigenas $jogosIndigena, JogosIndigenasEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);

        return view('coordenador.jogos-indigenas.edicoes.patrocinadores.index', [
            'jogo' => $jogosIndigena,
            'edicao' => $edicao,
            'patrocinadores' => $edicao->patrocinadores()->paginate(20)->withQueryString(),
        ]);
    }

    public function create(JogosIndigenas $jogosIndigena, JogosIndigenasEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);

        return view('coordenador.jogos-indigenas.edicoes.patrocinadores.create', [
            'jogo' => $jogosIndigena,
            'edicao' => $edicao,
            'patrocinador' => new JogosIndigenasEdicaoPatrocinador(['ordem' => 0]),
        ]);
    }

    public function store(
        SaveJogosIndigenasEdicaoPatrocinadorRequest $request,
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);

        DB::transaction(function () use ($request, $edicao) {
            $this->persist(new JogosIndigenasEdicaoPatrocinador(), $request, $edicao);
        });

        return redirect()
            ->route('coordenador.jogos-indigenas.edicoes.patrocinadores.index', [$jogosIndigena, $edicao])
            ->with('ok', 'Patrocinador cadastrado com sucesso.');
    }

    public function edit(
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao,
        JogosIndigenasEdicaoPatrocinador $patrocinador
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);
        abort_unless($patrocinador->jogos_indigenas_edicao_id === $edicao->id, 404);

        return view('coordenador.jogos-indigenas.edicoes.patrocinadores.edit', [
            'jogo' => $jogosIndigena,
            'edicao' => $edicao,
            'patrocinador' => $patrocinador,
        ]);
    }

    public function update(
        SaveJogosIndigenasEdicaoPatrocinadorRequest $request,
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao,
        JogosIndigenasEdicaoPatrocinador $patrocinador
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);
        abort_unless($patrocinador->jogos_indigenas_edicao_id === $edicao->id, 404);

        DB::transaction(function () use ($request, $edicao, $patrocinador) {
            $this->persist($patrocinador, $request, $edicao);
        });

        return redirect()
            ->route('coordenador.jogos-indigenas.edicoes.patrocinadores.index', [$jogosIndigena, $edicao])
            ->with('ok', 'Patrocinador atualizado com sucesso.');
    }

    public function destroy(
        JogosIndigenas $jogosIndigena,
        JogosIndigenasEdicao $edicao,
        JogosIndigenasEdicaoPatrocinador $patrocinador
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        $this->ensureEdicaoBelongsToJogo($jogosIndigena, $edicao);
        abort_unless($patrocinador->jogos_indigenas_edicao_id === $edicao->id, 404);

        DB::transaction(function () use ($patrocinador) {
            if ($patrocinador->logo_path) {
                Storage::disk('public')->delete($patrocinador->logo_path);
            }

            $patrocinador->delete();
        });

        return redirect()
            ->route('coordenador.jogos-indigenas.edicoes.patrocinadores.index', [$jogosIndigena, $edicao])
            ->with('ok', 'Patrocinador removido com sucesso.');
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
            ->with('erro', 'Os patrocinadores devem ser gerenciados a partir do cadastro principal de Jogos Indigenas.');
    }

    private function ensureEdicaoBelongsToJogo(JogosIndigenas $jogo, JogosIndigenasEdicao $edicao): void
    {
        abort_unless($edicao->jogos_indigenas_id === $jogo->id, 404);
    }

    private function persist(
        JogosIndigenasEdicaoPatrocinador $patrocinador,
        SaveJogosIndigenasEdicaoPatrocinadorRequest $request,
        JogosIndigenasEdicao $edicao
    ): JogosIndigenasEdicaoPatrocinador {
        $data = $request->validated();

        $patrocinador->fill([
            'jogos_indigenas_edicao_id' => $edicao->id,
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

            $patrocinador->logo_path = $request->file('logo')->store('jogos-indigenas/edicoes/patrocinadores', 'public');
        }

        $patrocinador->save();

        return $patrocinador;
    }
}

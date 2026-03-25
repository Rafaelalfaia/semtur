<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveJogosIndigenasRequest;
use App\Models\JogosIndigenas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JogosIndigenasController extends Controller
{
    public function index(Request $request)
    {
        $jogo = $this->principal()?->loadCount('edicoes');
        $totalRegistros = JogosIndigenas::query()->count();

        return view('coordenador.jogos-indigenas.index', [
            'jogo' => $jogo,
            'registrosExtras' => max($totalRegistros - 1, 0),
            'statuses' => JogosIndigenas::STATUS,
        ]);
    }

    public function create()
    {
        if ($principal = $this->principal()) {
            return redirect()
                ->route('coordenador.jogos-indigenas.edit', $principal)
                ->with('erro', 'Os Jogos Indigenas funcionam como cadastro unico. Edite o registro principal existente.');
        }

        return view('coordenador.jogos-indigenas.create', [
            'jogo' => new JogosIndigenas([
                'status' => JogosIndigenas::STATUS_RASCUNHO,
                'ordem' => 0,
            ]),
            'statuses' => JogosIndigenas::STATUS,
        ]);
    }

    public function store(SaveJogosIndigenasRequest $request)
    {
        if ($principal = $this->principal()) {
            return redirect()
                ->route('coordenador.jogos-indigenas.edit', $principal)
                ->with('erro', 'Ja existe um cadastro principal de Jogos Indigenas. Atualize o registro existente.');
        }

        $jogo = DB::transaction(function () use ($request) {
            return $this->persist(new JogosIndigenas(), $request);
        });

        return redirect()
            ->route('coordenador.jogos-indigenas.edit', $jogo)
            ->with('ok', 'Jogos Indigenas criado com sucesso.');
    }

    public function edit(JogosIndigenas $jogosIndigena)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        return view('coordenador.jogos-indigenas.edit', [
            'jogo' => $jogosIndigena->loadCount('edicoes'),
            'statuses' => JogosIndigenas::STATUS,
        ]);
    }

    public function update(SaveJogosIndigenasRequest $request, JogosIndigenas $jogosIndigena)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        DB::transaction(function () use ($request, $jogosIndigena) {
            $this->persist($jogosIndigena, $request);
        });

        return back()->with('ok', 'Jogos Indigenas atualizado com sucesso.');
    }

    public function destroy(JogosIndigenas $jogosIndigena)
    {
        if ($redirect = $this->redirectIfNotPrincipal($jogosIndigena)) {
            return $redirect;
        }

        DB::transaction(function () use ($jogosIndigena) {
            foreach ($jogosIndigena->edicoes()->with(['fotos', 'videos', 'patrocinadores'])->get() as $edicao) {
                $this->deleteEditionAssets($edicao);
                $edicao->fotos()->each->delete();
                $edicao->videos()->each->delete();
                $edicao->patrocinadores()->each->delete();
                $edicao->delete();
            }

            $jogosIndigena->delete();
        });

        return back()->with('ok', 'Jogos Indigenas movido para a lixeira.');
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
            ->route('coordenador.jogos-indigenas.edit', $principal)
            ->with('erro', 'O painel trabalha com um unico cadastro principal de Jogos Indigenas.');
    }

    private function persist(JogosIndigenas $jogo, SaveJogosIndigenasRequest $request): JogosIndigenas
    {
        $data = $request->validated();

        $jogo->fill([
            'titulo' => $data['titulo'],
            'slug' => $data['slug'] ?? null,
            'descricao' => $data['descricao'],
            'ordem' => $data['ordem'] ?? 0,
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? null,
        ]);

        if ($request->boolean('remover_foto_perfil') && $jogo->foto_perfil_path) {
            Storage::disk('public')->delete($jogo->foto_perfil_path);
            $jogo->foto_perfil_path = null;
        }

        if ($request->boolean('remover_foto_capa') && $jogo->foto_capa_path) {
            Storage::disk('public')->delete($jogo->foto_capa_path);
            $jogo->foto_capa_path = null;
        }

        if ($request->hasFile('foto_perfil')) {
            if ($jogo->foto_perfil_path) {
                Storage::disk('public')->delete($jogo->foto_perfil_path);
            }

            $jogo->foto_perfil_path = $request->file('foto_perfil')->store('jogos-indigenas/perfis', 'public');
        }

        if ($request->hasFile('foto_capa')) {
            if ($jogo->foto_capa_path) {
                Storage::disk('public')->delete($jogo->foto_capa_path);
            }

            $jogo->foto_capa_path = $request->file('foto_capa')->store('jogos-indigenas/capas', 'public');
        }

        $jogo->save();

        return $jogo;
    }

    private function deleteEditionAssets($edicao): void
    {
        if ($edicao->capa_path) {
            Storage::disk('public')->delete($edicao->capa_path);
        }

        foreach ($edicao->fotos as $foto) {
            if ($foto->imagem_path) {
                Storage::disk('public')->delete($foto->imagem_path);
            }
        }

        foreach ($edicao->patrocinadores as $patrocinador) {
            if ($patrocinador->logo_path) {
                Storage::disk('public')->delete($patrocinador->logo_path);
            }
        }
    }
}

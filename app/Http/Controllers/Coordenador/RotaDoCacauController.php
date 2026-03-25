<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRotaDoCacauRequest;
use App\Models\RotaDoCacau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RotaDoCacauController extends Controller
{
    public function index(Request $request)
    {
        $rota = $this->principal()?->loadCount('edicoes');
        $totalRegistros = RotaDoCacau::query()->count();

        return view('coordenador.rota-do-cacau.index', [
            'rota' => $rota,
            'registrosExtras' => max($totalRegistros - 1, 0),
            'statuses' => RotaDoCacau::STATUS,
        ]);
    }

    public function create()
    {
        if ($principal = $this->principal()) {
            return redirect()
                ->route('coordenador.rota-do-cacau.edit', $principal)
                ->with('erro', 'A Rota do Cacau funciona como cadastro unico. Edite o registro principal existente.');
        }

        return view('coordenador.rota-do-cacau.create', [
            'rota' => new RotaDoCacau([
                'status' => RotaDoCacau::STATUS_RASCUNHO,
                'ordem' => 0,
            ]),
            'statuses' => RotaDoCacau::STATUS,
        ]);
    }

    public function store(SaveRotaDoCacauRequest $request)
    {
        if ($principal = $this->principal()) {
            return redirect()
                ->route('coordenador.rota-do-cacau.edit', $principal)
                ->with('erro', 'Ja existe um cadastro principal de Rota do Cacau. Atualize o registro existente.');
        }

        $rota = DB::transaction(function () use ($request) {
            return $this->persist(new RotaDoCacau(), $request);
        });

        return redirect()
            ->route('coordenador.rota-do-cacau.edit', $rota)
            ->with('ok', 'Rota do Cacau criada com sucesso.');
    }

    public function edit(RotaDoCacau $rotaDoCacau)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        return view('coordenador.rota-do-cacau.edit', [
            'rota' => $rotaDoCacau->loadCount('edicoes'),
            'statuses' => RotaDoCacau::STATUS,
        ]);
    }

    public function update(SaveRotaDoCacauRequest $request, RotaDoCacau $rotaDoCacau)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        DB::transaction(function () use ($request, $rotaDoCacau) {
            $this->persist($rotaDoCacau, $request);
        });

        return back()->with('ok', 'Rota do Cacau atualizada com sucesso.');
    }

    public function destroy(RotaDoCacau $rotaDoCacau)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        DB::transaction(function () use ($rotaDoCacau) {
            foreach ($rotaDoCacau->edicoes()->with(['fotos', 'videos', 'patrocinadores'])->get() as $edicao) {
                $this->deleteEditionAssets($edicao);
                $edicao->fotos()->each->delete();
                $edicao->videos()->each->delete();
                $edicao->patrocinadores()->each->delete();
                $edicao->delete();
            }

            $rotaDoCacau->delete();
        });

        return back()->with('ok', 'Rota do Cacau movida para a lixeira.');
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
            ->route('coordenador.rota-do-cacau.edit', $principal)
            ->with('erro', 'O painel trabalha com um unico cadastro principal de Rota do Cacau.');
    }

    private function persist(RotaDoCacau $rota, SaveRotaDoCacauRequest $request): RotaDoCacau
    {
        $data = $request->validated();

        $rota->fill([
            'titulo' => $data['titulo'],
            'slug' => $this->resolveUniqueSlug(
                slug: $data['slug'] ?? null,
                titulo: $data['titulo'],
                ignoreId: $rota->id
            ),
            'descricao' => $data['descricao'],
            'ordem' => $data['ordem'] ?? 0,
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? null,
        ]);

        if ($request->boolean('remover_foto_perfil') && $rota->foto_perfil_path) {
            Storage::disk('public')->delete($rota->foto_perfil_path);
            $rota->foto_perfil_path = null;
        }

        if ($request->boolean('remover_foto_capa') && $rota->foto_capa_path) {
            Storage::disk('public')->delete($rota->foto_capa_path);
            $rota->foto_capa_path = null;
        }

        if ($request->hasFile('foto_perfil')) {
            if ($rota->foto_perfil_path) {
                Storage::disk('public')->delete($rota->foto_perfil_path);
            }

            $rota->foto_perfil_path = $request->file('foto_perfil')->store('rota-do-cacau/perfis', 'public');
        }

        if ($request->hasFile('foto_capa')) {
            if ($rota->foto_capa_path) {
                Storage::disk('public')->delete($rota->foto_capa_path);
            }

            $rota->foto_capa_path = $request->file('foto_capa')->store('rota-do-cacau/capas', 'public');
        }

        $rota->save();

        return $rota;
    }

    private function resolveUniqueSlug(?string $slug, string $titulo, ?int $ignoreId = null): string
    {
        $base = trim((string) ($slug ?: $titulo));
        $slugBase = Str::slug($base !== '' ? $base : 'rota-do-cacau');

        return RotaDoCacau::uniqueSlug($slugBase, $ignoreId);
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

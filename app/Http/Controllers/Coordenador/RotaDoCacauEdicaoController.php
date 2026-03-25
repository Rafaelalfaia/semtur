<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRotaDoCacauEdicaoRequest;
use App\Models\RotaDoCacau;
use App\Models\RotaDoCacauEdicao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RotaDoCacauEdicaoController extends Controller
{
    public function index(RotaDoCacau $rotaDoCacau)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $edicoes = $rotaDoCacau->edicoes()
            ->withCount(['fotos', 'videos', 'patrocinadores'])
            ->paginate(20)
            ->withQueryString();

        return view('coordenador.rota-do-cacau.edicoes.index', [
            'rota' => $rotaDoCacau,
            'edicoes' => $edicoes,
            'statuses' => RotaDoCacauEdicao::STATUS,
        ]);
    }

    public function create(RotaDoCacau $rotaDoCacau)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        return view('coordenador.rota-do-cacau.edicoes.create', [
            'rota' => $rotaDoCacau,
            'edicao' => new RotaDoCacauEdicao([
                'ano' => now()->year,
                'status' => RotaDoCacauEdicao::STATUS_RASCUNHO,
                'ordem' => 0,
            ]),
            'statuses' => RotaDoCacauEdicao::STATUS,
        ]);
    }

    public function store(SaveRotaDoCacauEdicaoRequest $request, RotaDoCacau $rotaDoCacau)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        $edicao = DB::transaction(function () use ($request, $rotaDoCacau) {
            return $this->persist(new RotaDoCacauEdicao(), $request, $rotaDoCacau);
        });

        return redirect()
            ->route('coordenador.rota-do-cacau.edicoes.edit', [$rotaDoCacau, $edicao])
            ->with('ok', 'Edicao criada com sucesso.');
    }

    public function edit(RotaDoCacau $rotaDoCacau, RotaDoCacauEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        abort_unless($edicao->rota_do_cacau_id === $rotaDoCacau->id, 404);

        return view('coordenador.rota-do-cacau.edicoes.edit', [
            'rota' => $rotaDoCacau,
            'edicao' => $edicao->loadCount(['fotos', 'videos', 'patrocinadores']),
            'statuses' => RotaDoCacauEdicao::STATUS,
        ]);
    }

    public function update(
        SaveRotaDoCacauEdicaoRequest $request,
        RotaDoCacau $rotaDoCacau,
        RotaDoCacauEdicao $edicao
    ) {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        abort_unless($edicao->rota_do_cacau_id === $rotaDoCacau->id, 404);

        DB::transaction(function () use ($request, $edicao, $rotaDoCacau) {
            $this->persist($edicao, $request, $rotaDoCacau);
        });

        return back()->with('ok', 'Edicao atualizada com sucesso.');
    }

    public function destroy(RotaDoCacau $rotaDoCacau, RotaDoCacauEdicao $edicao)
    {
        if ($redirect = $this->redirectIfNotPrincipal($rotaDoCacau)) {
            return $redirect;
        }

        abort_unless($edicao->rota_do_cacau_id === $rotaDoCacau->id, 404);

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
            ->route('coordenador.rota-do-cacau.edicoes.index', $rotaDoCacau)
            ->with('ok', 'Edicao movida para a lixeira.');
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
            ->with('erro', 'As edicoes devem ser gerenciadas a partir do cadastro principal de Rota do Cacau.');
    }

    private function persist(
        RotaDoCacauEdicao $edicao,
        SaveRotaDoCacauEdicaoRequest $request,
        RotaDoCacau $rota
    ): RotaDoCacauEdicao {
        $data = $request->validated();

        $edicao->fill([
            'rota_do_cacau_id' => $rota->id,
            'ano' => $data['ano'],
            'titulo' => $data['titulo'],
            'slug' => $this->resolveUniqueSlug(
                rota: $rota,
                slug: $data['slug'] ?? null,
                titulo: $data['titulo'],
                ignoreId: $edicao->id
            ),
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

            $edicao->capa_path = $request->file('capa')->store('rota-do-cacau/edicoes/capas', 'public');
        }

        $edicao->save();

        return $edicao;
    }

    private function resolveUniqueSlug(RotaDoCacau $rota, ?string $slug, string $titulo, ?int $ignoreId = null): string
    {
        $base = trim((string) ($slug ?: $titulo));
        $slugBase = Str::slug($base !== '' ? $base : 'edicao');

        return RotaDoCacauEdicao::uniqueSlug($slugBase, (int) $rota->id, $ignoreId);
    }
}

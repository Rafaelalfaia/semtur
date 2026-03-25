<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveVideoRequest;
use App\Models\Conteudo\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca', ''));
        $status = (string) $request->input('status', 'todos');

        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $videos = Video::query()
            ->when($status !== 'todos' && $status !== '', fn ($q) => $q->where('status', $status))
            ->when($busca !== '', function ($q) use ($busca, $like) {
                $q->where(function ($w) use ($busca, $like) {
                    $w->where('titulo', $like, "%{$busca}%")
                        ->orWhere('descricao', $like, "%{$busca}%");
                });
            })
            ->orderBy('ordem')
            ->orderByDesc('published_at')
            ->orderBy('titulo')
            ->paginate(12)
            ->withQueryString();

        return view('coordenador.videos.index', [
            'videos' => $videos,
            'busca' => $busca,
            'status' => $status,
            'statuses' => Video::STATUS,
        ]);
    }

    public function create()
    {
        return view('coordenador.videos.create', [
            'video' => new Video([
                'status' => Video::STATUS_RASCUNHO,
                'ordem' => 0,
            ]),
            'statuses' => Video::STATUS,
        ]);
    }

    public function store(SaveVideoRequest $request)
    {
        $video = DB::transaction(function () use ($request) {
            return $this->persist(new Video(), $request);
        });

        return redirect()
            ->route('coordenador.videos.edit', $video)
            ->with('ok', 'Vídeo criado com sucesso.');
    }

    public function edit(Video $video)
    {
        return view('coordenador.videos.edit', [
            'video' => $video,
            'statuses' => Video::STATUS,
        ]);
    }

    public function update(SaveVideoRequest $request, Video $video)
    {
        DB::transaction(function () use ($request, $video) {
            $this->persist($video, $request);
        });

        return back()->with('ok', 'Vídeo atualizado com sucesso.');
    }

    public function destroy(Video $video)
    {
        $video->delete();

        return back()->with('ok', 'Vídeo movido para a lixeira.');
    }

    public function publicar(Video $video)
    {
        $video->update([
            'status' => Video::STATUS_PUBLICADO,
            'published_at' => $video->published_at ?: now(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Vídeo publicado.');
    }

    public function arquivar(Video $video)
    {
        $video->update([
            'status' => Video::STATUS_ARQUIVADO,
            'published_at' => null,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Vídeo arquivado.');
    }

    public function rascunho(Video $video)
    {
        $video->update([
            'status' => Video::STATUS_RASCUNHO,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Vídeo movido para rascunho.');
    }

    private function persist(Video $video, SaveVideoRequest $request): Video
    {
        $data = $request->validated();

        $video->fill([
            'titulo' => $data['titulo'],
            'slug' => $data['slug'] ?? null,
            'descricao' => $data['descricao'],
            'link_acesso' => $data['link_acesso'],
            'ordem' => $data['ordem'] ?? 0,
            'status' => $data['status'],
        ]);

        if (!$video->exists) {
            $video->created_by = auth()->id();
        }

        $video->updated_by = auth()->id();

        if ($request->boolean('remover_capa') && $video->capa_path) {
            Storage::disk('public')->delete($video->capa_path);
            $video->capa_path = null;
        }

        if ($request->hasFile('capa')) {
            if ($video->capa_path) {
                Storage::disk('public')->delete($video->capa_path);
            }

            $video->capa_path = $request->file('capa')->store('videos/capas', 'public');
        }

        $video->save();

        return $video;
    }
}

<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Conteudo\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $videos = Video::publicados()
            ->when($q !== '', function ($query) use ($q, $like) {
                $query->where(function ($w) use ($q, $like) {
                    $w->where('titulo', $like, "%{$q}%")
                        ->orWhere('descricao', $like, "%{$q}%");
                });
            })
            ->orderBy('ordem')
            ->orderByDesc('published_at')
            ->orderBy('titulo')
            ->paginate(12)
            ->withQueryString();

        return view('site.videos.index', [
            'videos' => $videos,
            'q' => $q,
        ]);
    }

    public function show(string $slug)
    {
        $video = Video::publicados()
            ->where('slug', $slug)
            ->firstOrFail();

        $relacionados = Video::publicados()
            ->where('id', '<>', $video->id)
            ->orderBy('ordem')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('site.videos.show', [
            'video' => $video,
            'relacionados' => $relacionados,
        ]);
    }
}

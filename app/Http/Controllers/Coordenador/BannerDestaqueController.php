<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\Conteudo\BannerDestaque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;   // para o helper
use Illuminate\Http\UploadedFile;
use Intervention\Image\Encoders\WebpEncoder;




class BannerDestaqueController extends Controller
{
    public function index(Request $request)
    {
        $busca  = trim((string) $request->input('busca',''));
        $status = $request->input('status','todos');

        // LIKE compatível com Postgres/MySQL
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $q = BannerDestaque::query()
            ->when($status !== 'todos', fn($qq) => $qq->where('status',$status))
            ->when($busca !== '', fn($qq) => $qq->where('titulo', $like, "%{$busca}%"))
            ->ordenados();

        $banners = $q->paginate(20)->appends($request->query());

        return view('coordenador.banners_destaque.index', compact('banners','busca','status'));
    }

    public function create()
    {
        $banner = new BannerDestaque([
            'status' => 'publicado',
            'ordem'  => 0,
        ]);
        return view('coordenador.banners_destaque.create', compact('banner'));
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'titulo'             => ['nullable','string','max:160'],
        'subtitulo'          => ['nullable','string','max:255'],
        'link_url'           => ['nullable','string','max:500'],
        'target_blank'       => ['sometimes','boolean'],
        'cor_fundo'          => ['nullable','string','max:20'],
        'overlay_opacity'    => ['nullable','integer','min:0','max:100'],

        'status'             => ['nullable','string', Rule::in(['publicado','rascunho','arquivado'])],
        'ordem'              => ['nullable','integer','min:0'],
        'inicio_publicacao'  => ['nullable','date'],
        'fim_publicacao'     => ['nullable','date','after_or_equal:inicio_publicacao'],

        // Imagens
        'imagem_desktop'       => ['nullable','image','max:6144'],
        'imagem_mobile'        => ['nullable','image','max:6144'],
        'crop_imagem_desktop'  => ['nullable','string'],
        'crop_imagem_mobile'   => ['nullable','string'],

        // posição (enquadramento do preview)
        'pos_desktop'          => ['nullable','string'],
        'pos_mobile'           => ['nullable','string'],
    ]);

    $status   = $data['status'] ?? 'publicado';
    $ordem    = $data['ordem']  ?? 0;
    $target   = $request->boolean('target_blank');
    $cropDesk = $this->normalizeCrop($request->input('crop_imagem_desktop')) ?? [];
    $cropMob  = $this->normalizeCrop($request->input('crop_imagem_mobile')) ?? [];

    $posDesk  = $this->normalizePos($request->input('pos_desktop'));
    $posMob   = $this->normalizePos($request->input('pos_mobile'));

    DB::transaction(function () use ($request, $data, $status, $ordem, $target, $cropDesk, $cropMob, $posDesk, $posMob) {
        $payload = [
            'titulo'            => $data['titulo']            ?? null,
            'subtitulo'         => $data['subtitulo']         ?? null,
            'link_url'          => $data['link_url']          ?? null,
            'target_blank'      => $target,
            'cor_fundo'         => $data['cor_fundo']         ?? null,
            'overlay_opacity'   => $data['overlay_opacity']   ?? null,
            'status'            => $status,
            'ordem'             => $ordem,
            'inicio_publicacao' => $data['inicio_publicacao'] ?? null,
            'fim_publicacao'    => $data['fim_publicacao']    ?? null,
            'crop_desktop'      => $cropDesk ?: null,
            'crop_mobile'       => $cropMob  ?: null,
        ];

        if (Schema::hasColumn('banner_destaques','criado_por')) {
            $payload['criado_por'] = auth()->id();
        }

        /** @var \App\Models\Conteudo\BannerDestaque $banner */
        $banner = BannerDestaque::create($payload);

        if ($request->hasFile('imagem_desktop')) {
            $path = $this->salvarBannerCortado(
                $request->file('imagem_desktop'),
                $cropDesk,
                $posDesk,                          // << usa posição do preview
                "banners/{$banner->id}",
                "desktop",
                "desktop"
            );
            $banner->imagem_desktop_path = $path;
        }

        if ($request->hasFile('imagem_mobile')) {
            $path = $this->salvarBannerCortado(
                $request->file('imagem_mobile'),
                $cropMob,
                $posMob,                           // << idem
                "banners/{$banner->id}",
                "mobile",
                "mobile"
            );
            $banner->imagem_mobile_path = $path;
        }

        $banner->save();
    });

    cache()->forget('home:banner_principal');

    return redirect()
        ->route('coordenador.banners-destaque.index')
        ->with('ok','Banner principal criado com sucesso.');
}




    public function edit(BannerDestaque $banner)
    {
        return view('coordenador.banners_destaque.edit', compact('banner'));
    }

    public function update(Request $request, BannerDestaque $banner)
{
    $data = $request->validate([
        'titulo'             => ['nullable','string','max:160'],
        'subtitulo'          => ['nullable','string','max:255'],
        'link_url'           => ['nullable','string','max:500'],
        'target_blank'       => ['sometimes','boolean'],
        'cor_fundo'          => ['nullable','string','max:20'],
        'overlay_opacity'    => ['nullable','integer','min:0','max:100'],

        'status'             => ['nullable','string', Rule::in(['publicado','rascunho','arquivado'])],
        'ordem'              => ['nullable','integer','min:0'],
        'inicio_publicacao'  => ['nullable','date'],
        'fim_publicacao'     => ['nullable','date','after_or_equal:inicio_publicacao'],

        'imagem_desktop'       => ['nullable','image','max:6144'],
        'imagem_mobile'        => ['nullable','image','max:6144'],
        'crop_imagem_desktop'  => ['nullable','string'],
        'crop_imagem_mobile'   => ['nullable','string'],

        'pos_desktop'          => ['nullable','string'],
        'pos_mobile'           => ['nullable','string'],
    ]);

    $target   = $request->boolean('target_blank');
    $cropDesk = $this->normalizeCrop($request->input('crop_imagem_desktop'));
    $cropMob  = $this->normalizeCrop($request->input('crop_imagem_mobile'));

    $posDesk  = $this->normalizePos($request->input('pos_desktop'));
    $posMob   = $this->normalizePos($request->input('pos_mobile'));

    DB::transaction(function () use ($request, $data, $banner, $target, $cropDesk, $cropMob, $posDesk, $posMob) {
        $update = [
            'titulo'            => $data['titulo']            ?? $banner->titulo,
            'subtitulo'         => $data['subtitulo']         ?? $banner->subtitulo,
            'link_url'          => $data['link_url']          ?? $banner->link_url,
            'target_blank'      => $target,
            'cor_fundo'         => $data['cor_fundo']         ?? $banner->cor_fundo,
            'overlay_opacity'   => $data['overlay_opacity']   ?? $banner->overlay_opacity,
            'status'            => $data['status']            ?? $banner->status,
            'ordem'             => $data['ordem']             ?? $banner->ordem,
            'inicio_publicacao' => $data['inicio_publicacao'] ?? $banner->inicio_publicacao,
            'fim_publicacao'    => $data['fim_publicacao']    ?? $banner->fim_publicacao,
        ];

        if (!is_null($cropDesk)) $update['crop_desktop'] = $cropDesk;
        if (!is_null($cropMob))  $update['crop_mobile']  = $cropMob;

        if (Schema::hasColumn('banner_destaques','atualizado_por')) {
            $update['atualizado_por'] = auth()->id();
        }

        $banner->update($update);

        if ($request->hasFile('imagem_desktop')) {
            if ($banner->imagem_desktop_path && Storage::disk('public')->exists($banner->imagem_desktop_path)) {
                Storage::disk('public')->delete($banner->imagem_desktop_path);
            }
            $path = $this->salvarBannerCortado(
                $request->file('imagem_desktop'),
                $cropDesk ?? [],
                $posDesk,                          // << usa posição do preview
                "banners/{$banner->id}",
                "desktop",
                "desktop"
            );
            $banner->imagem_desktop_path = $path;
        }

        if ($request->hasFile('imagem_mobile')) {
            if ($banner->imagem_mobile_path && Storage::disk('public')->exists($banner->imagem_mobile_path)) {
                Storage::disk('public')->delete($banner->imagem_mobile_path);
            }
            $path = $this->salvarBannerCortado(
                $request->file('imagem_mobile'),
                $cropMob ?? [],
                $posMob,                           // << idem
                "banners/{$banner->id}",
                "mobile",
                "mobile"
            );
            $banner->imagem_mobile_path = $path;
        }

        $banner->save();
    });

    cache()->forget('home:banner_principal');

    return redirect()
        ->route('coordenador.banners-destaque.index')
        ->with('ok','Banner principal atualizado.');
}




    private function salvarBannerCortado(
        UploadedFile $file,
        array $crop,
        ?array $pos,              // ['x'=>0..100,'y'=>0..100] ou null
        string $destDir,
        string $nome,
        string $alvo
    ): string
    {
        $targets = [
            'desktop' => ['w'=>1920,'h'=>700],
            'mobile'  => ['w'=>1080,'h'=>1080],
        ];
        $tw = $targets[$alvo]['w'];
        $th = $targets[$alvo]['h'];
        $targetRatio = $tw / $th;

        $manager = new ImageManager(new Driver());
        $img = $manager->read($file->getRealPath());

        // 1) Se veio crop do front, aplica exatamente
        if (!empty($crop) && isset($crop['width'], $crop['height'])) {
            if (!empty($crop['rotate'])) $img->rotate(-(float)$crop['rotate']);
            if (!empty($crop['scaleX']) && $crop['scaleX'] < 0) $img->flip('h');
            if (!empty($crop['scaleY']) && $crop['scaleY'] < 0) $img->flip('v');

            $x = max(0, (int) round($crop['x'] ?? 0));
            $y = max(0, (int) round($crop['y'] ?? 0));
            $w = (int) round($crop['width']);
            $h = (int) round($crop['height']);

            $w = min($w, $img->width()  - $x);
            $h = min($h, $img->height() - $y);

            if ($w > 0 && $h > 0) {
                $img->crop($w, $h, $x, $y);
            }
        }
        // 2) Senão, “cover” no ratio alvo respeitando a posição (se enviada)
        else {
            $sw = $img->width();
            $sh = $img->height();
            if ($sw > 0 && $sh > 0) {
                $srcRatio = $sw / $sh;
                if ($srcRatio > $targetRatio) {
                    // muito larga → define cropW pelo alvo
                    $cropH = $sh;
                    $cropW = (int) round($sh * $targetRatio);
                } else {
                    // muito alta → define cropH pelo alvo
                    $cropW = $sw;
                    $cropH = (int) round($sw / $targetRatio);
                }

                // posição (%), 0=início, 50=centro, 100=fim
                $px = isset($pos['x']) ? max(0, min(100, (float)$pos['x'])) : 50.0;
                $py = isset($pos['y']) ? max(0, min(100, (float)$pos['y'])) : 50.0;

                $maxX = max(0, $sw - $cropW);
                $maxY = max(0, $sh - $cropH);

                $x = (int) round($maxX * ($px / 100));
                $y = (int) round($maxY * ($py / 100));

                $img->crop($cropW, $cropH, $x, $y);
            }
        }

        // 3) Redimensiona para o alvo (ratio já equalizado, não distorce)
        $img->resize($tw, $th);

        $path = trim($destDir,'/')."/{$nome}.webp";
        $bin = $img->encode(new WebpEncoder(quality: 86));
        Storage::disk('public')->put($path, (string) $bin);
        return $path;
    }



    private function normalizePos($raw): ?array
    {
        if (is_array($raw) && isset($raw['x'],$raw['y'])) {
            return ['x'=>(float)$raw['x'], 'y'=>(float)$raw['y']];
        }
        if (is_string($raw) && $raw !== '') {
            if (preg_match('/^\s*([\d.]+)\s*[, ]\s*([\d.]+)\s*%?\s*$/', $raw, $m)) {
                return ['x'=>(float)$m[1], 'y'=>(float)$m[2]];
            }
        }
        return null;
    }


    public function destroy(BannerDestaque $banner)
    {
        // Remover mídias
        foreach (['imagem_desktop_path','imagem_mobile_path'] as $col) {
            if ($banner->$col && Storage::disk('public')->exists($banner->$col)) {
                Storage::disk('public')->delete($banner->$col);
            }
        }
        $banner->delete();

        cache()->forget('home:banner_principal');

        return back()->with('ok','Banner removido.');
    }

    public function toggle(BannerDestaque $banner)
    {
        $banner->update([
            'status' => $banner->status === 'publicado' ? 'rascunho' : 'publicado',
        ]);

        cache()->forget('home:banner_principal');

        return back()->with('ok','Status atualizado.');
    }

    public function reordenar(Request $request)
    {
        $ids = $request->validate(['ids'=>'required|array'])['ids'];
        foreach ($ids as $ordem => $id) {
            BannerDestaque::whereKey($id)->update(['ordem'=>$ordem]);
        }
        cache()->forget('home:banner_principal');

        return response()->json(['ok'=>true]);
    }

    /**
     * Normaliza o JSON vindo do front (string -> array).
     * Retorna null se vazio/ inválido (não sobrescreve o existente).
     */
    private function normalizeCrop($raw)
    {
        if (is_array($raw)) return $raw;
        if (is_string($raw) && $raw !== '') {
            try {
                $arr = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                return is_array($arr) ? $arr : null;
            } catch (\Throwable $e) {
                return null;
            }
        }
        return null;
    }
}

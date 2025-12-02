<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Conteudo\Banner;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::query()->orderByDesc('created_at')->paginate(15);
        return view('coordenador.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('coordenador.banners.form', ['banner' => new Banner()]);
    }

    public function store(Request $r)
{
    $data = $r->validate([
        'titulo'     => ['nullable','string','max:120'],
        'subtitulo'  => ['nullable','string','max:180'],
        'cta_label'  => ['nullable','string','max:60'],
        'cta_url'    => ['nullable','url'],
        'ordem'      => ['nullable','integer','min:0'],
        'status'     => ['required','in:rascunho,publicado,arquivado'],
        'imagem'     => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
        'pos_banner'   => ['nullable','string'],            // "x,y"
        'pos_banner_x' => ['nullable','numeric','min:0','max:100'],
        'pos_banner_y' => ['nullable','numeric','min:0','max:100'],
    ]);

    $data['ordem'] = isset($data['ordem']) && $data['ordem'] !== '' ? (int) $data['ordem'] : 0;

    [$fx,$fy] = $this->normalizePos(
        $r->input('pos_banner'),
        $r->input('pos_banner_x'),
        $r->input('pos_banner_y'),
    );

    $banner = new Banner($data);
    $banner->created_by = auth()->id();
    $banner->pos_banner_x = $fx;
    $banner->pos_banner_y = $fy;

    if ($r->hasFile('imagem')) {
        // salva ORIGINAL
        $banner->imagem_original_path = $r->file('imagem')->store('banners','public');
        // gera DERIVADA respeitando foco
        $banner->imagem_path = $this->renderBannerCrop($banner->imagem_original_path, $fx, $fy);
    }

    if ($banner->status === Banner::STATUS_PUBLICADO && !$banner->published_at) {
        $banner->published_at = now();
    }

    $banner->save();

    cache()->forget('home:banner');
    cache()->forget('home:categorias');

    return redirect()->route('coordenador.banners.index')->with('ok','Banner criado.');
}


    public function edit(Banner $banner)
    {
        return view('coordenador.banners.form', compact('banner'));
    }

    public function update(Request $r, Banner $banner)
    {
        $data = $r->validate([
            'titulo'     => ['nullable','string','max:120'],
            'subtitulo'  => ['nullable','string','max:180'],
            'cta_label'  => ['nullable','string','max:60'],
            'cta_url'    => ['nullable','url'],
            'ordem'      => ['nullable','integer','min:0'],
            'status'     => ['required','in:rascunho,publicado,arquivado'],
            'imagem'     => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'pos_banner'   => ['nullable','string'],            // "x,y"
            'pos_banner_x' => ['nullable','numeric','min:0','max:100'],
            'pos_banner_y' => ['nullable','numeric','min:0','max:100'],
        ]);

        $data['ordem'] = isset($data['ordem']) && $data['ordem'] !== '' ? (int) $data['ordem'] : 0;

        [$fx,$fy] = $this->normalizePos(
            $r->input('pos_banner'),
            $r->input('pos_banner_x'),
            $r->input('pos_banner_y'),
        );

        // Preenche o MESMO registro (não crie um novo!)
        $banner->fill($data);
        $banner->pos_banner_x = $fx;
        $banner->pos_banner_y = $fy;

        $oldOriginal = $banner->imagem_original_path;
        $oldDerived  = $banner->imagem_path;

        if ($r->hasFile('imagem')) {
            // remove arquivos antigos
            if ($oldOriginal) Storage::disk('public')->delete($oldOriginal);
            if ($oldDerived)  Storage::disk('public')->delete($oldDerived);

            // salva NOVA ORIGINAL
            $banner->imagem_original_path = $r->file('imagem')->store('banners','public');
            // gera nova derivada com o foco atual
            $banner->imagem_path = $this->renderBannerCrop($banner->imagem_original_path, $fx, $fy);
        } else {
            // sem novo arquivo: recorte novamente a partir da ORIGINAL usando o novo foco
            if ($banner->imagem_original_path) {
                if ($oldDerived) Storage::disk('public')->delete($oldDerived);
                $banner->imagem_path = $this->renderBannerCrop($banner->imagem_original_path, $fx, $fy);
            }
        }

        // published_at
        if ($banner->status === Banner::STATUS_PUBLICADO && !$banner->published_at) {
            $banner->published_at = now();
        }
        if ($banner->status !== Banner::STATUS_PUBLICADO) {
            $banner->published_at = null;
        }

        $banner->save();

        cache()->forget('home:banner');
        cache()->forget('home:categorias');

        return redirect()->route('coordenador.banners.index')->with('ok','Banner atualizado.');
    }


    private function normalizePos(?string $combined, $x = null, $y = null): array
    {
        $fx = null; $fy = null;

        // tenta parsear "x,y" ou "x y" (aceita opcional "%" no final do segundo número)
        if (is_string($combined) && trim($combined) !== '') {
            $s = trim($combined);
            if (preg_match('/^\s*([\d.]+)\s*,\s*([\d.]+)\s*%?\s*$/', $s, $m)
            || preg_match('/^\s*([\d.]+)\s+([\d.]+)\s*%?\s*$/', $s, $m)) {
                $fx = (float)$m[1];
                $fy = (float)$m[2];
            }
        }

        if ($fx === null && $x !== null) $fx = (float)$x;
        if ($fy === null && $y !== null) $fy = (float)$y;

        // defaults e clamp
        $fx = max(0, min(100, $fx ?? 50.0));
        $fy = max(0, min(100, $fy ?? 50.0));

        return [$fx, $fy];
    }

   private function renderBannerCrop(string $originalPath, float $x, float $y): string
{
    // defina o alvo do banner (mesmo ratio da Home)
    $targetW = 1800;
    $targetH = 600;

    $disk = Storage::disk('public');
    $abs  = $disk->path($originalPath);

    $manager = new ImageManager(new Driver());
    $img = $manager->read($abs);

    // ===== cover com foco (x,y) em % =====
    $srcW = $img->width();
    $srcH = $img->height();
    $targetRatio = $targetW / $targetH;
    $srcRatio    = $srcW / $srcH;

    if ($srcRatio > $targetRatio) {
        $cropH = $srcH;
        $cropW = (int) round($srcH * $targetRatio);
    } else {
        $cropW = $srcW;
        $cropH = (int) round($srcW / $targetRatio);
    }

    $overflowX = max(0, $srcW - $cropW);
    $overflowY = max(0, $srcH - $cropH);

    $offsetX = (int) round(($x / 100) * $overflowX);
    $offsetY = (int) round(($y / 100) * $overflowY);

    $offsetX = max(0, min($overflowX, $offsetX));
    $offsetY = max(0, min($overflowY, $offsetY));

    $img = $img->crop($cropW, $cropH, $offsetX, $offsetY)
               ->resize($targetW, $targetH);

    // ===== nome da derivada (com foco no nome para quebrar cache) =====
    $ext  = strtolower(pathinfo($originalPath, PATHINFO_EXTENSION) ?: 'jpg');
    $base = preg_replace('/(\.[a-z0-9]+)$/i', '', $originalPath);
    $fx = (int) round($x * 10); // 0.1%
    $fy = (int) round($y * 10);
    $derivedPath = "{$base}_{$targetW}x{$targetH}_f{$fx}-{$fy}.{$ext}";

    // cria pasta se faltar
    $disk->makeDirectory(dirname($derivedPath));

    // ===== v3: encode -> save =====
    switch ($ext) {
        case 'png':
            $encoded = $img->toPng();                  // sem qualidade para PNG
            break;
        case 'webp':
            // se GD/Imagick sem suporte a webp, faça try/catch e caia para JPEG
            try { $encoded = $img->toWebp(quality: 85); }
            catch (\Throwable $e) { $encoded = $img->toJpeg(quality: 85); }
            break;
        default: // jpg/jpeg
            $encoded = $img->toJpeg(quality: 85);
    }

    $encoded->save($disk->path($derivedPath)); // <- aqui não dá mais o erro

    return $derivedPath;
}

    public function destroy(Banner $banner)
{
    if ($banner->imagem_path)        Storage::disk('public')->delete($banner->imagem_path);
    if ($banner->imagem_original_path) Storage::disk('public')->delete($banner->imagem_original_path);

    $banner->delete();

    cache()->forget('home:banner');
    cache()->forget('home:categorias');

    return redirect()->route('coordenador.banners.index')->with('ok','Banner removido.');
}

}

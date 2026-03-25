<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveBannerDestaqueRequest;
use App\Models\Conteudo\BannerDestaque;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class BannerDestaqueController extends Controller
{
    private const IMAGE_COLUMNS = [
        'imagem_desktop_path',
        'imagem_mobile_path',
        'poster_desktop_path',
        'poster_mobile_path',
        'fallback_image_desktop_path',
        'fallback_image_mobile_path',
    ];

    private const VIDEO_COLUMNS = [
        'video_desktop_path',
        'video_mobile_path',
    ];

    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca', ''));
        $status = $request->input('status', 'todos');

        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $banners = BannerDestaque::query()
            ->when($status !== 'todos', fn ($query) => $query->where('status', $status))
            ->when($busca !== '', fn ($query) => $query->where('titulo', $like, "%{$busca}%"))
            ->ordenados()
            ->paginate(20)
            ->appends($request->query());

        return view('coordenador.banners_destaque.index', compact('banners', 'busca', 'status'));
    }

    public function create()
    {
        $banner = new BannerDestaque([
            'status' => BannerDestaque::STATUS_PUBLICADO,
            'ordem' => 0,
            'media_type' => BannerDestaque::MEDIA_IMAGE,
            'autoplay' => true,
            'loop' => true,
            'muted' => true,
            'hero_variant' => 'hero',
            'preload_mode' => 'metadata',
        ]);

        return view('coordenador.banners_destaque.create', compact('banner'));
    }

    public function store(SaveBannerDestaqueRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {
            $payload = $this->payloadFromData($data);

            if (Schema::hasColumn('banner_destaques', 'criado_por')) {
                $payload['criado_por'] = auth()->id();
            }

            $banner = BannerDestaque::create($payload);

            $this->syncMedia($banner, $request);
        });

        cache()->forget('home:banner_principal');

        return redirect()
            ->route('coordenador.banners-destaque.index')
            ->with('ok', 'Banner principal criado com sucesso.');
    }

    public function edit(BannerDestaque $banner)
    {
        return view('coordenador.banners_destaque.edit', compact('banner'));
    }

    public function update(SaveBannerDestaqueRequest $request, BannerDestaque $banner)
    {
        $data = $request->validated();

        DB::transaction(function () use ($banner, $request, $data) {
            $payload = $this->payloadFromData($data);

            if (Schema::hasColumn('banner_destaques', 'atualizado_por')) {
                $payload['atualizado_por'] = auth()->id();
            }

            $banner->update($payload);
            $this->syncMedia($banner, $request);
        });

        cache()->forget('home:banner_principal');

        return redirect()
            ->route('coordenador.banners-destaque.index')
            ->with('ok', 'Banner principal atualizado.');
    }

    public function destroy(BannerDestaque $banner)
    {
        foreach (array_merge(self::IMAGE_COLUMNS, self::VIDEO_COLUMNS) as $column) {
            $this->deleteStoredFile($banner->{$column});
        }

        $banner->delete();

        cache()->forget('home:banner_principal');

        return back()->with('ok', 'Banner removido.');
    }

    public function toggle(BannerDestaque $banner)
    {
        $banner->update([
            'status' => $banner->status === BannerDestaque::STATUS_PUBLICADO
                ? BannerDestaque::STATUS_RASCUNHO
                : BannerDestaque::STATUS_PUBLICADO,
        ]);

        cache()->forget('home:banner_principal');

        return back()->with('ok', 'Status atualizado.');
    }

    public function reordenar(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array'])['ids'];

        foreach ($ids as $ordem => $id) {
            BannerDestaque::whereKey($id)->update(['ordem' => $ordem]);
        }

        cache()->forget('home:banner_principal');

        return response()->json(['ok' => true]);
    }

    private function payloadFromData(array $data): array
    {
        return [
            'titulo' => $data['titulo'] ?? null,
            'subtitulo' => $data['subtitulo'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'target_blank' => $data['target_blank'] ?? false,
            'media_type' => $data['media_type'],
            'cor_fundo' => $data['cor_fundo'] ?? null,
            'overlay_opacity' => $data['overlay_opacity'] ?? null,
            'autoplay' => $data['autoplay'] ?? true,
            'loop' => $data['loop'] ?? true,
            'muted' => $data['muted'] ?? true,
            'hero_variant' => $data['hero_variant'] ?? null,
            'preload_mode' => $data['preload_mode'] ?? 'metadata',
            'alt_text' => $data['alt_text'] ?? null,
            'status' => $data['status'] ?? BannerDestaque::STATUS_PUBLICADO,
            'ordem' => $data['ordem'] ?? 0,
            'inicio_publicacao' => $data['inicio_publicacao'] ?? null,
            'fim_publicacao' => $data['fim_publicacao'] ?? null,
            'crop_desktop' => $this->normalizeCrop($data['crop_imagem_desktop'] ?? null),
            'crop_mobile' => $this->normalizeCrop($data['crop_imagem_mobile'] ?? null),
        ];
    }

    private function syncMedia(BannerDestaque $banner, SaveBannerDestaqueRequest $request): void
    {
        $cropDesk = $this->normalizeCrop($request->input('crop_imagem_desktop')) ?? [];
        $cropMob = $this->normalizeCrop($request->input('crop_imagem_mobile')) ?? [];
        $posDesk = $this->normalizePos($request->input('pos_desktop'));
        $posMob = $this->normalizePos($request->input('pos_mobile'));

        if ($request->hasFile('imagem_desktop')) {
            $this->deleteStoredFile($banner->imagem_desktop_path);
            $banner->imagem_desktop_path = $this->salvarBannerCortado(
                $request->file('imagem_desktop'),
                $cropDesk,
                $posDesk,
                "banners/{$banner->id}",
                'desktop',
                'desktop'
            );
        }

        if ($request->hasFile('imagem_mobile')) {
            $this->deleteStoredFile($banner->imagem_mobile_path);
            $banner->imagem_mobile_path = $this->salvarBannerCortado(
                $request->file('imagem_mobile'),
                $cropMob,
                $posMob,
                "banners/{$banner->id}",
                'mobile',
                'mobile'
            );
        }

        foreach ([
            'video_desktop' => 'video_desktop_path',
            'video_mobile' => 'video_mobile_path',
            'poster_desktop' => 'poster_desktop_path',
            'poster_mobile' => 'poster_mobile_path',
            'fallback_image_desktop' => 'fallback_image_desktop_path',
            'fallback_image_mobile' => 'fallback_image_mobile_path',
        ] as $input => $column) {
            if ($request->hasFile($input)) {
                $this->deleteStoredFile($banner->{$column});
                $banner->{$column} = $this->storeRawAsset($request->file($input), "banners/{$banner->id}", $input);
            }
        }

        $banner->save();
    }

    private function storeRawAsset(UploadedFile $file, string $directory, string $prefix): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');

        return $file->storeAs(
            trim($directory, '/'),
            "{$prefix}.{$extension}",
            'public'
        );
    }

    private function deleteStoredFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function salvarBannerCortado(
        UploadedFile $file,
        array $crop,
        ?array $pos,
        string $destDir,
        string $nome,
        string $alvo
    ): string {
        $targets = [
            'desktop' => ['w' => 1920, 'h' => 700],
            'mobile' => ['w' => 1080, 'h' => 1080],
        ];
        $tw = $targets[$alvo]['w'];
        $th = $targets[$alvo]['h'];
        $targetRatio = $tw / $th;

        $manager = new ImageManager(new Driver());
        $img = $manager->read($file->getRealPath());

        if (! empty($crop) && isset($crop['width'], $crop['height'])) {
            if (! empty($crop['rotate'])) {
                $img->rotate(-(float) $crop['rotate']);
            }
            if (! empty($crop['scaleX']) && $crop['scaleX'] < 0) {
                $img->flip('h');
            }
            if (! empty($crop['scaleY']) && $crop['scaleY'] < 0) {
                $img->flip('v');
            }

            $x = max(0, (int) round($crop['x'] ?? 0));
            $y = max(0, (int) round($crop['y'] ?? 0));
            $w = (int) round($crop['width']);
            $h = (int) round($crop['height']);

            $w = min($w, $img->width() - $x);
            $h = min($h, $img->height() - $y);

            if ($w > 0 && $h > 0) {
                $img->crop($w, $h, $x, $y);
            }
        } else {
            $sw = $img->width();
            $sh = $img->height();
            if ($sw > 0 && $sh > 0) {
                if (($sw / $sh) > $targetRatio) {
                    $cropH = $sh;
                    $cropW = (int) round($sh * $targetRatio);
                } else {
                    $cropW = $sw;
                    $cropH = (int) round($sw / $targetRatio);
                }

                $px = isset($pos['x']) ? max(0, min(100, (float) $pos['x'])) : 50.0;
                $py = isset($pos['y']) ? max(0, min(100, (float) $pos['y'])) : 50.0;

                $maxX = max(0, $sw - $cropW);
                $maxY = max(0, $sh - $cropH);

                $x = (int) round($maxX * ($px / 100));
                $y = (int) round($maxY * ($py / 100));

                $img->crop($cropW, $cropH, $x, $y);
            }
        }

        $img->resize($tw, $th);

        $path = trim($destDir, '/')."/{$nome}.webp";
        $bin = $img->encode(new WebpEncoder(quality: 86));
        Storage::disk('public')->put($path, (string) $bin);

        return $path;
    }

    private function normalizePos($raw): ?array
    {
        if (is_array($raw) && isset($raw['x'], $raw['y'])) {
            return ['x' => (float) $raw['x'], 'y' => (float) $raw['y']];
        }

        if (is_string($raw) && $raw !== '' && preg_match('/^\s*([\d.]+)\s*[, ]\s*([\d.]+)\s*%?\s*$/', $raw, $m)) {
            return ['x' => (float) $m[1], 'y' => (float) $m[2]];
        }

        return null;
    }

    private function normalizeCrop($raw)
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            try {
                $arr = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

                return is_array($arr) ? $arr : null;
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}

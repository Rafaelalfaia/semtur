<?php

namespace App\Models\Conteudo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BannerDestaque extends Model
{
    protected $table = 'banner_destaques';

    public const STATUS_PUBLICADO = 'publicado';
    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_ARQUIVADO = 'arquivado';

    public const MEDIA_IMAGE = 'image';
    public const MEDIA_VIDEO = 'video';

    protected const CACHE_KEYS = [
        'home:banner_topo',
        'home:banner_principal',
    ];

    protected $fillable = [
        'titulo',
        'subtitulo',
        'link_url',
        'target_blank',
        'media_type',
        'cor_fundo',
        'overlay_opacity',
        'autoplay',
        'loop',
        'muted',
        'hero_variant',
        'preload_mode',
        'alt_text',
        'status',
        'ordem',
        'inicio_publicacao',
        'fim_publicacao',
        'imagem_desktop_path',
        'imagem_mobile_path',
        'video_desktop_path',
        'video_mobile_path',
        'poster_desktop_path',
        'poster_mobile_path',
        'fallback_image_desktop_path',
        'fallback_image_mobile_path',
        'crop_desktop',
        'crop_mobile',
    ];

    protected $casts = [
        'target_blank' => 'bool',
        'autoplay' => 'bool',
        'loop' => 'bool',
        'muted' => 'bool',
        'ordem' => 'integer',
        'overlay_opacity' => 'integer',
        'inicio_publicacao' => 'datetime',
        'fim_publicacao' => 'datetime',
        'crop_desktop' => 'array',
        'crop_mobile' => 'array',
    ];

    protected $appends = [
        'imagem_desktop_url',
        'imagem_mobile_url',
        'desktop_url',
        'mobile_url',
        'href',
        'cor',
        'ativo_agora',
        'publicado',
        'video_desktop_url',
        'video_mobile_url',
        'poster_desktop_url',
        'poster_mobile_url',
        'fallback_image_desktop_url',
        'fallback_image_mobile_url',
        'video_valido',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => static::clearHomeCaches());
        static::deleted(fn () => static::clearHomeCaches());
    }

    protected static function clearHomeCaches(): void
    {
        foreach (self::CACHE_KEYS as $key) {
            cache()->forget($key);
        }
    }

    protected function publicUrlFromPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    protected function sanitizeHref(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $url = trim($url);

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
            return $url;
        }

        return '#';
    }

    protected function imagemDesktopUrl(): Attribute
    {
        return Attribute::get(fn () => $this->publicUrlFromPath($this->imagem_desktop_path));
    }

    protected function imagemMobileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->publicUrlFromPath($this->imagem_mobile_path));
    }

    protected function videoDesktopUrl(): Attribute
    {
        return Attribute::get(fn () => $this->publicUrlFromPath($this->video_desktop_path));
    }

    protected function videoMobileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->publicUrlFromPath($this->video_mobile_path));
    }

    protected function posterDesktopUrl(): Attribute
    {
        return Attribute::get(fn () => $this->publicUrlFromPath($this->poster_desktop_path));
    }

    protected function posterMobileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->publicUrlFromPath($this->poster_mobile_path));
    }

    protected function fallbackImageDesktopUrl(): Attribute
    {
        return Attribute::get(fn () => $this->publicUrlFromPath($this->fallback_image_desktop_path));
    }

    protected function fallbackImageMobileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->publicUrlFromPath($this->fallback_image_mobile_path));
    }

    protected function desktopUrl(): Attribute
    {
        return Attribute::get(fn () => $this->fallback_image_desktop_url ?: $this->imagem_desktop_url);
    }

    protected function mobileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->fallback_image_mobile_url ?: $this->imagem_mobile_url ?: $this->desktop_url);
    }

    protected function cor(): Attribute
    {
        return Attribute::get(fn () => $this->cor_fundo);
    }

    protected function href(): Attribute
    {
        return Attribute::get(fn () => $this->sanitizeHref($this->link_url));
    }

    protected function publicado(): Attribute
    {
        return Attribute::get(fn () => $this->status === self::STATUS_PUBLICADO);
    }

    protected function ativoAgora(): Attribute
    {
        return Attribute::get(function () {
            $now = Carbon::now();

            $inicioOk = ! $this->inicio_publicacao || $this->inicio_publicacao->lte($now);
            $fimOk = ! $this->fim_publicacao || $this->fim_publicacao->gte($now);

            return $inicioOk && $fimOk;
        });
    }

    protected function videoValido(): Attribute
    {
        return Attribute::get(fn () => $this->hasValidVideo());
    }

    public function hasValidVideo(): bool
    {
        if ($this->media_type !== self::MEDIA_VIDEO) {
            return false;
        }

        return filled($this->video_desktop_path) || filled($this->video_mobile_path);
    }

    public function resolvedPosterDesktopUrl(?string $fallback = null): ?string
    {
        return $this->poster_desktop_url
            ?: $this->fallback_image_desktop_url
            ?: $this->imagem_desktop_url
            ?: $fallback;
    }

    public function resolvedPosterMobileUrl(?string $fallback = null): ?string
    {
        return $this->poster_mobile_url
            ?: $this->fallback_image_mobile_url
            ?: $this->imagem_mobile_url
            ?: $this->resolvedPosterDesktopUrl($fallback);
    }

    public function scopePublicados($query)
    {
        return $query->where('status', self::STATUS_PUBLICADO);
    }

    public function scopeAtivosAgora($query)
    {
        $now = Carbon::now();

        return $query
            ->where(function ($q) use ($now) {
                $q->whereNull('inicio_publicacao')
                    ->orWhere('inicio_publicacao', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('fim_publicacao')
                    ->orWhere('fim_publicacao', '>=', $now);
            });
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem')->orderByDesc('id');
    }
}

<?php

namespace App\Models\Conteudo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BannerDestaque extends Model
{
    // Tabela (garante compatibilidade com a migration)
    protected $table = 'banner_destaques';

    /** Status aceitos */
    public const STATUS_PUBLICADO = 'publicado';
    public const STATUS_RASCUNHO  = 'rascunho';
    public const STATUS_ARQUIVADO = 'arquivado';

    /** Chaves de cache que a Home pode usar */
    protected const CACHE_KEYS = [
        'home:banner_topo',       // usada na Home atual
        'home:banner_principal',  // usada em alguns pontos do backoffice
    ];

    /** Atributos preenchíveis */
    protected $fillable = [
        'titulo',
        'subtitulo',
        'link_url',
        'target_blank',
        'cor_fundo',
        'overlay_opacity',
        'status',
        'ordem',
        'inicio_publicacao',
        'fim_publicacao',
        'imagem_desktop_path',
        'imagem_mobile_path',
        'crop_desktop',
        'crop_mobile',
    ];

    /** Casts coerentes com o uso */
    protected $casts = [
        'target_blank'      => 'bool',
        'ordem'             => 'integer',
        'overlay_opacity'   => 'integer',   // ou 'float' se preferir
        'inicio_publicacao' => 'datetime',
        'fim_publicacao'    => 'datetime',
        'crop_desktop'      => 'array',     // json/jsonb na migration
        'crop_mobile'       => 'array',
    ];

    /**
     * Atributos "append" úteis quando o model vira array/JSON.
     * (No Blade, não é obrigatório — os accessors já funcionam.)
     */
    protected $appends = [
        'imagem_desktop_url',
        'imagem_mobile_url',
        'desktop_url',
        'mobile_url',
        'href',
        'cor',
        'ativo_agora',
        'publicado',
    ];

    /* -----------------------------------------
     | Boot: limpa o cache da Home ao salvar/apagar
     ------------------------------------------*/
    protected static function booted(): void
    {
        static::saved(function () {
            static::clearHomeCaches();
        });

        static::deleted(function () {
            static::clearHomeCaches();
        });
    }

    protected static function clearHomeCaches(): void
    {
        foreach (self::CACHE_KEYS as $key) {
            cache()->forget($key);
        }
    }

    /* -----------------------------------------
     | Helpers internos
     ------------------------------------------*/
    protected function publicUrlFromPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Se já for URL absoluta (http/https ou protocolo relativo), retorna como está
        if (str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, '//')) {
            return $path;
        }

        // Caso contrário, assume disk 'public'
        return Storage::disk('public')->url($path);
    }

    protected function sanitizeHref(?string $url): ?string
    {
        if (!$url) {
            return null;
        }
        $url = trim($url);

        // Aceita http/https e caminhos internos começando com '/'
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
            return $url;
        }

        // Qualquer outra coisa, melhor cair no '#'
        return '#';
    }

    /* -----------------------------------------
     | Accessors (Laravel 10/11 style)
     | Observação: nome camelCase mapeia para snake_case no acesso dinâmico
     | Ex.: imagemDesktopUrl() => $model->imagem_desktop_url
     ------------------------------------------*/

    // URL pública da imagem desktop (padrão principal)
    protected function imagemDesktopUrl(): Attribute
    {
        return Attribute::get(function () {
            return $this->publicUrlFromPath($this->imagem_desktop_path);
        });
    }

    // URL pública da imagem mobile (padrão principal)
    protected function imagemMobileUrl(): Attribute
    {
        return Attribute::get(function () {
            return $this->publicUrlFromPath($this->imagem_mobile_path);
        });
    }

    // Aliases para compatibilidade com views que esperam desktop_url/mobile_url
    protected function desktopUrl(): Attribute
    {
        return Attribute::get(fn () => $this->imagem_desktop_url);
    }

    protected function mobileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->imagem_mobile_url);
    }

    // Cor com alias genérico "cor" (algumas views usam esse nome)
    protected function cor(): Attribute
    {
        return Attribute::get(fn () => $this->cor_fundo);
    }

    // href saneado para ser usado diretamente na view
    protected function href(): Attribute
    {
        return Attribute::get(fn () => $this->sanitizeHref($this->link_url));
    }

    // Flags úteis na view/controller
    protected function publicado(): Attribute
    {
        return Attribute::get(fn () => $this->status === self::STATUS_PUBLICADO);
    }

    protected function ativoAgora(): Attribute
    {
        return Attribute::get(function () {
            $now = Carbon::now();

            $inicioOk = !$this->inicio_publicacao || $this->inicio_publicacao->lte($now);
            $fimOk    = !$this->fim_publicacao || $this->fim_publicacao->gte($now);

            return $inicioOk && $fimOk;
        });
    }

    /* -----------------------------------------
     | Scopes
     ------------------------------------------*/

    /**
     * Somente registros com status "publicado".
     */
    public function scopePublicados($query)
    {
        return $query->where('status', self::STATUS_PUBLICADO);
    }

    /**
     * Dentro da janela de publicação (tolerante a null).
     */
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

    /**
     * Ordenação padrão: ordem ASC, id DESC (estável).
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem')->orderByDesc('id');
    }
}

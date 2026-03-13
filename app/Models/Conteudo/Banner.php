<?php

namespace App\Models\Conteudo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use SoftDeletes;

    protected $table = 'banners';

    public const STATUS_PUBLICADO = 'publicado';
    public const STATUS_RASCUNHO  = 'rascunho';
    public const STATUS_ARQUIVADO = 'arquivado';

    protected $fillable = [
        'titulo',
        'subtitulo',
        'cta_label',
        'cta_url',
        'imagem_path',
        'imagem_original_path',
        'pos_banner_x',
        'pos_banner_y',
        'ordem',
        'status',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'pos_banner_x' => 'float',
        'pos_banner_y' => 'float',
    ];

    protected $appends = [
        'imagem_url',
        'imagem_original_url',
        'is_publicado',
    ];

    protected $attributes = [
        'status' => self::STATUS_RASCUNHO,
        'ordem'  => 0,
        'pos_banner_x' => 50,
        'pos_banner_y' => 50,
    ];

    public function getImagemUrlAttribute(): ?string
    {
        if (!$this->imagem_path) {
            return null;
        }

        $url = Storage::disk('public')->url($this->imagem_path);

        $ver = ($this->updated_at?->timestamp ?? time())
            . '-' . (int) round(($this->pos_banner_x ?? 50) * 10)
            . '-' . (int) round(($this->pos_banner_y ?? 50) * 10);

        return $url . '?v=' . $ver;
    }

    public function getImagemOriginalUrlAttribute(): ?string
    {
        return $this->imagem_original_path
            ? Storage::disk('public')->url($this->imagem_original_path)
            : null;
    }

    public function getIsPublicadoAttribute(): bool
    {
        return $this->status === self::STATUS_PUBLICADO;
    }

    public function scopePublicados($q)
    {
        return $q->where('status', self::STATUS_PUBLICADO);
    }

    public function scopeOrdenado($q)
    {
        return $q->orderBy('ordem')->orderBy('id');
    }
}

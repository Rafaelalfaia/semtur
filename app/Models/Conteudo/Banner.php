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
        'titulo','subtitulo','cta_label','cta_url',
        'imagem_path','ordem','status','published_at','created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = ['imagem_url','is_publicado'];

    public function getImagemUrlAttribute(): ?string
    {
        return $this->imagem_path ? Storage::url($this->imagem_path) : null;
    }

    protected $attributes = [
        'status' => self::STATUS_RASCUNHO,
        'ordem'  => 0,
    ];


    public function getIsPublicadoAttribute(): bool
    {
        return $this->status === self::STATUS_PUBLICADO;
    }

    public function scopePublicados($q) { return $q->where('status', self::STATUS_PUBLICADO); }
    public function scopeOrdenado($q)   { return $q->orderBy('ordem')->orderBy('id'); }
}

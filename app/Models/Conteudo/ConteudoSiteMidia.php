<?php

namespace App\Models\Conteudo;

use App\Models\Idioma;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ConteudoSiteMidia extends Model
{
    protected $table = 'conteudo_site_midias';

    protected $fillable = [
        'conteudo_site_bloco_id',
        'idioma_id',
        'slot',
        'disk',
        'path',
        'alt_text',
        'legenda',
        'mime_type',
        'largura',
        'altura',
        'tamanho_bytes',
        'focal_x',
        'focal_y',
        'ordem',
        'configuracao',
    ];

    protected $casts = [
        'configuracao' => 'array',
        'focal_x' => 'decimal:2',
        'focal_y' => 'decimal:2',
    ];

    protected $appends = [
        'url',
    ];

    public function bloco(): BelongsTo
    {
        return $this->belongsTo(ConteudoSiteBloco::class, 'conteudo_site_bloco_id');
    }

    public function idioma(): BelongsTo
    {
        return $this->belongsTo(Idioma::class, 'idioma_id');
    }

    public function getUrlAttribute(): ?string
    {
        if (! filled($this->path)) {
            return null;
        }

        return Storage::disk($this->disk ?: 'public')->url($this->path);
    }
}

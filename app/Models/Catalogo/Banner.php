<?php

// app/Models/Conteudo/Banner.php
namespace App\Models\Conteudo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'titulo','subtitulo','cta_label','cta_url',
        'ordem','status',
        'imagem_path','imagem_original_path',
        // não precisam estar no fillable se você setar manualmente no controller,
        // mas pode incluir:
        'pos_banner_x','pos_banner_y','published_at',
    ];

    protected $casts = [
        'pos_banner_x' => 'float',
        'pos_banner_y' => 'float',
        'published_at' => 'datetime',
    ];

    // usados no Blade: $banner->imagem_url e ->imagem_original_url
    public function getImagemUrlAttribute(): ?string
    {
        if (!$this->imagem_path) return null;

        $url = Storage::disk('public')->url($this->imagem_path);

        // versão baseada no updated_at + foco (garante mudança mesmo se horários coincidirem)
        $ver = ($this->updated_at?->timestamp ?? time())
            .'-'.(int)round(($this->pos_banner_x ?? 50) * 10)
            .'-'.(int)round(($this->pos_banner_y ?? 50) * 10);

        return $url.'?v='.$ver;
    }
    public function getImagemOriginalUrlAttribute(): ?string
    {
        return $this->imagem_original_path ? Storage::disk('public')->url($this->imagem_original_path) : null;
    }



    // se tiver constantes de status, mantenha aqui...
}

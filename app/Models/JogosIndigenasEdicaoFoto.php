<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class JogosIndigenasEdicaoFoto extends Model
{
    use SoftDeletes;

    protected $table = 'jogos_indigenas_edicao_fotos';

    protected $fillable = [
        'jogos_indigenas_edicao_id',
        'imagem_path',
        'legenda',
        'ordem',
    ];

    protected $casts = [
        'ordem' => 'integer',
    ];

    protected $attributes = [
        'ordem' => 0,
    ];

    protected $appends = [
        'imagem_url',
    ];

    public function edicao()
    {
        return $this->belongsTo(JogosIndigenasEdicao::class, 'jogos_indigenas_edicao_id');
    }

    public function getImagemUrlAttribute(): ?string
    {
        return $this->imagem_path
            ? Storage::disk('public')->url($this->imagem_path)
            : null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class RotaDoCacauEdicaoFoto extends Model
{
    use SoftDeletes;

    protected $table = 'rota_do_cacau_edicao_fotos';

    protected $fillable = [
        'rota_do_cacau_edicao_id',
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
        return $this->belongsTo(RotaDoCacauEdicao::class, 'rota_do_cacau_edicao_id');
    }

    public function getImagemUrlAttribute(): ?string
    {
        return $this->imagem_path
            ? Storage::disk('public')->url($this->imagem_path)
            : null;
    }
}

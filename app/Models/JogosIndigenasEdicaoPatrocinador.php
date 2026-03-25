<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class JogosIndigenasEdicaoPatrocinador extends Model
{
    use SoftDeletes;

    protected $table = 'jogos_indigenas_edicao_patrocinadores';

    protected $fillable = [
        'jogos_indigenas_edicao_id',
        'nome',
        'logo_path',
        'url',
        'ordem',
    ];

    protected $casts = [
        'ordem' => 'integer',
    ];

    protected $attributes = [
        'ordem' => 0,
    ];

    protected $appends = [
        'logo_url',
    ];

    public function edicao()
    {
        return $this->belongsTo(JogosIndigenasEdicao::class, 'jogos_indigenas_edicao_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : null;
    }
}

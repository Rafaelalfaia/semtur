<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class RotaDoCacauEdicaoPatrocinador extends Model
{
    use SoftDeletes;

    protected $table = 'rota_do_cacau_edicao_patrocinadores';

    protected $fillable = [
        'rota_do_cacau_edicao_id',
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
        return $this->belongsTo(RotaDoCacauEdicao::class, 'rota_do_cacau_edicao_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : null;
    }
}

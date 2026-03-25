<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class EmpresaFoto extends Model
{
    use SoftDeletes;

    protected $table = 'empresa_fotos';

    protected $fillable = [
        'empresa_id',
        'path',
        'alt',
        'ordem',
    ];

    protected $casts = [
        'ordem' => 'integer',
    ];

    protected $appends = [
        'url',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk('public')->url($this->path) : null;
    }
}

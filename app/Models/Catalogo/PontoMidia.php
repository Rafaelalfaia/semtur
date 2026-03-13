<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PontoMidia extends Model
{
    use SoftDeletes;

    protected $table = 'ponto_midias';

    protected $fillable = [
        'ponto_turistico_id',
        'tipo',
        'path',
        'url',
        'thumb_path',
        'ordem',
    ];

    protected $attributes = [
        'ordem' => 0,
        'tipo'  => 'image',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function ponto()
    {
        return $this->belongsTo(PontoTuristico::class, 'ponto_turistico_id');
    }

    public function getUrlAttribute(): ?string
    {
        if (in_array($this->tipo, ['image', 'video', 'video_file'], true)) {
            return $this->path ? Storage::disk('public')->url($this->path) : null;
        }

        if ($this->tipo === 'video_link') {
            return $this->attributes['url'] ?? null;
        }

        return null;
    }

    public function getPathUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk('public')->url($this->path) : null;
    }

    public function url(): ?string
    {
        return $this->getUrlAttribute();
    }
}

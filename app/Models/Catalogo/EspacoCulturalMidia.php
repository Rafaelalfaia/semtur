<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class EspacoCulturalMidia extends Model
{
    use SoftDeletes;

    protected $table = 'espaco_cultural_midias';

    protected $fillable = [
        'espaco_cultural_id',
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

    public function espaco()
    {
        return $this->belongsTo(EspacoCultural::class, 'espaco_cultural_id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk('public')->url($this->path) : null;
    }
}

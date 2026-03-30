<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Idioma extends Model
{
    protected $table = 'idiomas';

    protected $fillable = [
        'codigo',
        'nome',
        'sigla',
        'bandeira',
        'html_lang',
        'hreflang',
        'og_locale',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function getBandeiraUrlAttribute(): ?string
    {
        $value = trim((string) ($this->bandeira ?? ''));

        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset(ltrim($value, '/'));
    }
}

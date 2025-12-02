<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Secretaria extends Model
{
    use SoftDeletes;

    protected $table = 'secretaria';

    protected $fillable = [
        'nome','slug','descricao','redes',
        'maps_url','endereco','bairro','cidade','lat','lng',
        'foto_path','foto_capa_path',
        'ordem','status','published_at','created_by'
    ];

    protected $casts = [
        'redes' => 'array',
        'published_at' => 'datetime',
    ];

    /* Accessors */
    public function getFotoUrlAttribute(): ?string {
        return $this->foto_path ? Storage::url($this->foto_path) : null;
    }
    public function getFotoCapaUrlAttribute(): ?string {
        return $this->foto_capa_path ? Storage::url($this->foto_capa_path) : null;
    }

    /* Singleton helper */
    public static function instance(): self
    {
        return static::query()->first() ?? static::create([
            'nome' => 'SEMTUR',
            'slug' => 'semtur',
            'status' => 'publicado',
        ]);
    }

    /* Parse lat/lng de links do Google Maps */
    public static function parseLatLngFromMaps(string $url): array
    {
        if (preg_match('/@(-?\d+\.\d+),\s*(-?\d+\.\d+)/', $url, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }
        if (preg_match('/[?&]q=(-?\d+\.\d+),\s*(-?\d+\.\d+)/', $url, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }
        return [null, null];
    }

    protected static function booted(): void
    {
        static::saving(function(self $m){
            if (blank($m->slug)) $m->slug = Str::slug($m->nome);
            if ($m->maps_url && (blank($m->lat) || blank($m->lng))) {
                [$lat,$lng] = self::parseLatLngFromMaps($m->maps_url);
                if ($lat && $lng) { $m->lat=$lat; $m->lng=$lng; }
            }
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Evento extends Model
{
    protected $fillable = [
        'nome','slug','cidade','regiao','descricao',
        'capa_path','perfil_path','rating','status',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (blank($m->slug)) $m->slug = Str::slug($m->nome);
        });
    }

    // /eventos/{slug}
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function edicoes()
    {
        return $this->hasMany(EventoEdicao::class)->orderByDesc('ano');
    }

    // Scopes
    public function scopePublicado($q) { return $q->where('status','publicado'); }
}

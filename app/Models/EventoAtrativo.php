<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EventoAtrativo extends Model
{
    protected $table = 'evento_atrativos'; // 👈 fix
    protected $fillable = [
        'edicao_id','nome','slug','descricao','thumb_path','ordem','status',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (blank($m->slug)) $m->slug = Str::slug($m->nome);
        });
    }

    public function edicao(){ return $this->belongsTo(EventoEdicao::class,'edicao_id'); }
    public function scopePublicado($q){ return $q->where('status','publicado'); }
}

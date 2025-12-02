<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class PontoRecomendacao extends Model
{
    use SoftDeletes;

    protected $table = 'ponto_recomendacoes';

    protected $fillable = [
        'ponto_turistico_id',
        'categoria_id',
        'ordem',
        'inicio_em',
        'fim_em',
        'ativo_forcado',
    ];

    protected $casts = [
        'inicio_em'     => 'datetime',
        'fim_em'        => 'datetime',
        'ativo_forcado' => 'boolean',
    ];

    public function ponto()
    {
        return $this->belongsTo(PontoTuristico::class, 'ponto_turistico_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function scopeAtivas(Builder $q): Builder
    {
        $now = now();
        return $q->where(function ($w) use ($now) {
            $w->where('ativo_forcado', true)
              ->orWhere(fn($p)=>$p
                  ->where(fn($d)=>$d->whereNull('inicio_em')->orWhere('inicio_em','<=',$now))
                  ->where(fn($d)=>$d->whereNull('fim_em')->orWhere('fim_em','>=',$now))
              );
        });
    }
}

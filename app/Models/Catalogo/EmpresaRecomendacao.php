<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class EmpresaRecomendacao extends Model
{
    use SoftDeletes;
    protected $table = 'empresa_recomendacoes';


    protected $fillable = [
        'empresa_id','categoria_id','inicio_em','fim_em','ativo_forcado','ordem',
    ];

    protected $casts = [
        'inicio_em' => 'datetime',
        'fim_em' => 'datetime',
        'ativo_forcado' => 'boolean',
    ];

    public function empresa(){ return $this->belongsTo(Empresa::class); }
    public function categoria(){ return $this->belongsTo(Categoria::class); }

    public function scopeAtivas(Builder $q): Builder
    {
        $now = now();
        return $q->where(function ($w) use ($now) {
            $w->where('ativo_forcado', true)
              ->orWhere(fn($p)=>$p->where(fn($d)=>$d->whereNull('inicio_em')->orWhere('inicio_em','<=',$now))
                                  ->where(fn($d)=>$d->whereNull('fim_em')->orWhere('fim_em','>=',$now)));
        });
    }
}

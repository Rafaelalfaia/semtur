<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;

class RoteiroEmpresa extends Model
{
    protected $table = 'roteiro_empresas';

    protected $fillable = [
        'roteiro_id',
        'empresa_id',
        'tipo_sugestao',
        'ordem',
        'destaque',
        'observacao_curta',
    ];

    protected $casts = [
        'destaque' => 'boolean',
    ];

    protected $appends = ['tipo_sugestao_label'];

    public function roteiro()
    {
        return $this->belongsTo(Roteiro::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function getTipoSugestaoLabelAttribute(): string
    {
        return Roteiro::TIPOS_SUGESTAO[$this->tipo_sugestao] ?? $this->tipo_sugestao;
    }
}

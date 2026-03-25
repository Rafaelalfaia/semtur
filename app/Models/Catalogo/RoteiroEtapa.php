<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;

class RoteiroEtapa extends Model
{
    protected $table = 'roteiro_etapas';

    protected $fillable = [
        'roteiro_id',
        'titulo',
        'subtitulo',
        'descricao',
        'tipo_bloco',
        'ordem',
    ];

    protected $appends = ['tipo_bloco_label'];

    public function roteiro()
    {
        return $this->belongsTo(Roteiro::class);
    }

    public function pontos()
    {
        return $this->hasMany(RoteiroEtapaPonto::class)->orderBy('ordem')->orderBy('id');
    }

    public function getTipoBlocoLabelAttribute(): string
    {
        return Roteiro::TIPOS_BLOCO[$this->tipo_bloco] ?? $this->tipo_bloco;
    }
}

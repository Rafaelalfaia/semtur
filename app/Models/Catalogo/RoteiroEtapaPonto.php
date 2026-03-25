<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;

class RoteiroEtapaPonto extends Model
{
    protected $table = 'roteiro_etapa_pontos';

    protected $fillable = [
        'roteiro_etapa_id',
        'ponto_turistico_id',
        'ordem',
        'destaque',
        'observacao_curta',
        'tempo_estimado_min',
    ];

    protected $casts = [
        'destaque' => 'boolean',
    ];

    public function etapa()
    {
        return $this->belongsTo(RoteiroEtapa::class, 'roteiro_etapa_id');
    }

    public function pontoTuristico()
    {
        return $this->belongsTo(PontoTuristico::class, 'ponto_turistico_id');
    }
}

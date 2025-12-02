<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventoEdicao extends Model
{
    protected $table = 'evento_edicoes'; // já tínhamos corrigido

    protected $fillable = [
        'evento_id','ano','data_inicio','data_fim','local','resumo',
        'lat','lng','status',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim'    => 'date',
        'lat'         => 'decimal:6',
        'lng'         => 'decimal:6',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    // 👇 INFORME A FK 'edicao_id' AQUI
    public function atrativos()
    {
        return $this->hasMany(EventoAtrativo::class, 'edicao_id')->orderBy('ordem');
    }

    // 👇 E AQUI TAMBÉM
    public function midias()
    {
        return $this->hasMany(EventoMidia::class, 'edicao_id')->orderBy('ordem');
    }

    public function getPeriodoAttribute(): string
    {
        $ini = $this->data_inicio?->format('d/m');
        $fim = $this->data_fim?->format('d/m');
        return $ini || $fim ? trim(($ini ? "$ini — " : '').($fim ?: '')) : (string)$this->ano;
    }

    public function scopePublicado($q){ return $q->where('status','publicado'); }
}

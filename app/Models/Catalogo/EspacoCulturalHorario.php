<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;

class EspacoCulturalHorario extends Model
{
    protected $table = 'espaco_cultural_horarios';

    public const DIAS = [
        0 => 'Domingo',
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
    ];

    protected $fillable = [
        'espaco_cultural_id',
        'dia_semana',
        'hora_inicio',
        'hora_fim',
        'vagas',
        'observacao',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'dia_semana' => 'integer',
        'vagas' => 'integer',
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    protected $appends = [
        'dia_label',
        'faixa_label',
    ];

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function espaco()
    {
        return $this->belongsTo(EspacoCultural::class, 'espaco_cultural_id');
    }

    public function agendamentos()
    {
        return $this->hasMany(EspacoCulturalAgendamento::class, 'espaco_cultural_horario_id');
    }

    public function getDiaLabelAttribute(): string
    {
        return self::DIAS[$this->dia_semana] ?? 'Dia';
    }

    public function getFaixaLabelAttribute(): string
    {
        return substr((string) $this->hora_inicio, 0, 5) . ' às ' . substr((string) $this->hora_fim, 0, 5);
    }
}

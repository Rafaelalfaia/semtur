<?php

namespace App\Services;

use App\Models\Catalogo\EspacoCultural;
use App\Models\Catalogo\EspacoCulturalAgendamento;
use App\Models\Catalogo\EspacoCulturalHorario;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class DisponibilidadeEspacoCulturalService
{
    public function ensureDisponivel(
        EspacoCultural $espaco,
        EspacoCulturalHorario $horario,
        Carbon $dataVisita,
        int $qtdVisitantes
    ): void {
        if ($dataVisita->lt(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'data_visita' => 'Escolha uma data igual ou posterior a hoje.',
            ]);
        }

        if ((int) $dataVisita->dayOfWeek !== (int) $horario->dia_semana) {
            throw ValidationException::withMessages([
                'espaco_cultural_horario_id' => 'O horário selecionado não corresponde ao dia da data informada.',
            ]);
        }

        $restantes = $this->remainingSlots($espaco, $horario, $dataVisita->toDateString());

        if (!is_null($restantes) && $qtdVisitantes > $restantes) {
            throw ValidationException::withMessages([
                'qtd_visitantes' => "Restam apenas {$restantes} vaga(s) para esta data e horário.",
            ]);
        }
    }

    public function remainingSlots(
        EspacoCultural $espaco,
        EspacoCulturalHorario $horario,
        string $dataVisita
    ): ?int {
        if (is_null($horario->vagas)) {
            return null;
        }

        $ocupadas = EspacoCulturalAgendamento::query()
            ->where('espaco_cultural_id', $espaco->id)
            ->where('espaco_cultural_horario_id', $horario->id)
            ->whereDate('data_visita', $dataVisita)
            ->whereIn('status', EspacoCulturalAgendamento::STATUSES_QUE_CONSOMEM_VAGA)
            ->sum('qtd_visitantes');

        return max(0, (int) $horario->vagas - (int) $ocupadas);
    }
}

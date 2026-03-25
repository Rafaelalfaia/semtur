<?php

namespace App\Services;

use App\Models\Catalogo\EspacoCultural;
use App\Models\Catalogo\EspacoCulturalHorario;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SyncHorariosService
{
    public function sync(EspacoCultural $espaco, array $rows): void
    {
        $items = $this->normalize($rows);
        $this->validateRows($espaco, $items);

        $existing = $espaco->horarios()->get()->keyBy('id');
        $keptIds = [];

        foreach ($items as $item) {
            $id = $item['id'] ?? null;
            $payload = Arr::except($item, ['id']);

            if ($id) {
                /** @var EspacoCulturalHorario $horario */
                $horario = $existing->get($id);

                if (!$horario) {
                    throw ValidationException::withMessages([
                        'horarios' => 'Há um horário inválido na edição. Recarregue a página e tente novamente.',
                    ]);
                }

                $horario->fill($payload);
                $horario->save();

                $keptIds[] = $horario->id;
                continue;
            }

            $novo = $espaco->horarios()->create($payload);
            $keptIds[] = $novo->id;
        }

        $idsParaRemover = $existing->keys()->diff($keptIds)->values();

        foreach ($idsParaRemover as $id) {
            /** @var EspacoCulturalHorario $horario */
            $horario = $existing->get($id);

            if ($horario->agendamentos()->exists()) {
                throw ValidationException::withMessages([
                    'horarios' => 'Não é possível remover o horário ' . $this->label($horario) . ' porque ele já possui agendamentos vinculados.',
                ]);
            }

            $horario->delete();
        }
    }

    private function normalize(array $rows): Collection
    {
        return collect($rows)
            ->map(function ($row, $i) {
                return [
                    'id' => filled($row['id'] ?? null) ? (int) $row['id'] : null,
                    'dia_semana' => isset($row['dia_semana']) ? (int) $row['dia_semana'] : null,
                    'hora_inicio' => trim((string) ($row['hora_inicio'] ?? '')),
                    'hora_fim' => trim((string) ($row['hora_fim'] ?? '')),
                    'vagas' => filled($row['vagas'] ?? null) ? (int) $row['vagas'] : null,
                    'observacao' => trim((string) ($row['observacao'] ?? '')) ?: null,
                    'ativo' => (bool) ($row['ativo'] ?? true),
                    'ordem' => isset($row['ordem']) ? (int) $row['ordem'] : $i,
                ];
            })
            ->filter(fn ($row) =>
                $row['dia_semana'] !== null &&
                $row['hora_inicio'] !== '' &&
                $row['hora_fim'] !== ''
            )
            ->values();
    }

    private function validateRows(EspacoCultural $espaco, Collection $items): void
    {
        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'horarios' => 'Informe pelo menos um horário válido.',
            ]);
        }

        $keys = [];

        foreach ($items as $index => $item) {
            if ($item['hora_fim'] <= $item['hora_inicio']) {
                throw ValidationException::withMessages([
                    'horarios' => 'No horário #' . ($index + 1) . ' a hora final precisa ser maior que a inicial.',
                ]);
            }

            $key = implode('|', [
                $item['dia_semana'],
                $item['hora_inicio'],
                $item['hora_fim'],
            ]);

            if (in_array($key, $keys, true)) {
                throw ValidationException::withMessages([
                    'horarios' => 'Existem horários duplicados no mesmo dia/faixa.',
                ]);
            }

            $keys[] = $key;
        }
    }

    private function label(EspacoCulturalHorario $horario): string
    {
        return $horario->dia_label . ' • ' . $horario->faixa_label . ' • ' . $horario->espaco?->nome;
    }
}

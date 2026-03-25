<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEspacoCulturalAgendamentoRequest;
use App\Models\Catalogo\EspacoCultural;
use App\Models\Catalogo\EspacoCulturalAgendamento;
use App\Models\Catalogo\EspacoCulturalHorario;
use App\Services\DisponibilidadeEspacoCulturalService;
use Carbon\Carbon;

class EspacoCulturalAgendamentoPublicController extends Controller
{
    public function __construct(
        private DisponibilidadeEspacoCulturalService $disponibilidadeService
    ) {}

    private array $diasSemana = [
        0 => 'Domingo',
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
    ];

    private array $statusLabels = [
        EspacoCulturalAgendamento::STATUS_PENDENTE => 'Pendente',
        EspacoCulturalAgendamento::STATUS_EM_ANALISE => 'Em análise',
        EspacoCulturalAgendamento::STATUS_CONFIRMADO => 'Confirmado',
        EspacoCulturalAgendamento::STATUS_CANCELADO => 'Cancelado',
        EspacoCulturalAgendamento::STATUS_CONCLUIDO => 'Concluído',
        EspacoCulturalAgendamento::STATUS_EXPIRADO => 'Expirado',
    ];

    public function create(EspacoCultural $espaco)
    {
        $this->ensureBookable($espaco);

        $horarios = $espaco->horarios()
            ->ativos()
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        return view('site.espacos_culturais.agendar', [
            'espaco' => $espaco,
            'horarios' => $horarios,
            'diasSemana' => $this->diasSemana,
        ]);
    }

    public function store(StoreEspacoCulturalAgendamentoRequest $request, EspacoCultural $espaco)
    {
        $this->ensureBookable($espaco);

        $validated = $request->validated();
        $dataVisita = Carbon::parse($validated['data_visita'])->startOfDay();

        /** @var EspacoCulturalHorario|null $horario */
        $horario = $espaco->horarios()
            ->ativos()
            ->whereKey($validated['espaco_cultural_horario_id'])
            ->first();

        abort_unless($horario, 422);

        $this->disponibilidadeService->ensureDisponivel(
            $espaco,
            $horario,
            $dataVisita,
            (int) $validated['qtd_visitantes']
        );

        $agendamento = new EspacoCulturalAgendamento();
        $agendamento->fill([
            'espaco_cultural_id' => $espaco->id,
            'espaco_cultural_horario_id' => $horario->id,
            'data_visita' => $dataVisita->toDateString(),
            'protocolo' => $this->generateProtocol($espaco),
            'nome' => $validated['nome'],
            'telefone' => $validated['telefone'],
            'email' => $validated['email'] ?? null,
            'qtd_visitantes' => (int) $validated['qtd_visitantes'],
            'observacao_visitante' => $validated['observacao_visitante'] ?? null,
            'status' => EspacoCulturalAgendamento::STATUS_PENDENTE,
            'whatsapp_phone' => $espaco->agendamento_whatsapp_phone,
        ]);

        $agendamento->whatsapp_message = $this->buildWhatsappMessage($espaco, $horario, $agendamento);
        $agendamento->save();

        return redirect()
            ->route('site.museus.agendamentos.show', $agendamento->protocolo)
            ->with('ok', 'Solicitação registrada com sucesso.');
    }

    public function show(string $protocolo)
    {
        $agendamento = EspacoCulturalAgendamento::query()
            ->with(['espaco', 'horario'])
            ->where('protocolo', $protocolo)
            ->firstOrFail();

        return view('site.espacos_culturais.status', [
            'agendamento' => $agendamento,
            'statusLabels' => $this->statusLabels,
            'diasSemana' => $this->diasSemana,
        ]);
    }

    public function whatsapp(string $protocolo)
    {
        $agendamento = EspacoCulturalAgendamento::query()
            ->where('protocolo', $protocolo)
            ->firstOrFail();

        abort_unless($agendamento->whatsapp_link, 404);

        $changes = [];

        if (is_null($agendamento->whatsapp_clicked_at)) {
            $changes['whatsapp_clicked_at'] = now();
        }

        if ($agendamento->status === EspacoCulturalAgendamento::STATUS_PENDENTE) {
            $changes['status'] = EspacoCulturalAgendamento::STATUS_EM_ANALISE;
        }

        if ($changes) {
            $agendamento->fill($changes)->save();
        }

        return redirect()->away($agendamento->whatsapp_link);
    }

    private function ensureBookable(EspacoCultural $espaco): void
    {
        abort_unless($espaco->status === EspacoCultural::STATUS_PUBLICADO, 404);
        abort_unless($espaco->agendamento_disponivel, 404);
    }

    private function generateProtocol(EspacoCultural $espaco): string
    {
        do {
            $prefixo = $espaco->tipo === EspacoCultural::TIPO_TEATRO ? 'TEC' : 'MUS';
            $codigo = $prefixo . '-' . now()->format('Ymd') . '-' . strtoupper(substr(md5(uniqid((string) $espaco->id, true)), 0, 6));
        } while (
            EspacoCulturalAgendamento::query()->where('protocolo', $codigo)->exists()
        );

        return $codigo;
    }

    private function buildWhatsappMessage(
        EspacoCultural $espaco,
        EspacoCulturalHorario $horario,
        EspacoCulturalAgendamento $agendamento
    ): string {
        $data = Carbon::parse($agendamento->data_visita)->format('d/m/Y');
        $dia = $this->diasSemana[$horario->dia_semana] ?? 'Dia';

        $linhas = [
            'Olá! Gostaria de solicitar agendamento de visita.',
            '',
            'Espaço: ' . $espaco->nome,
            'Tipo: ' . $espaco->tipo_label,
            'Data desejada: ' . $data,
            'Dia: ' . $dia,
            'Horário: ' . $horario->faixa_label,
            'Visitantes: ' . $agendamento->qtd_visitantes,
            'Responsável: ' . $agendamento->nome,
            'Telefone: ' . $agendamento->telefone,
        ];

        if ($agendamento->email) {
            $linhas[] = 'E-mail: ' . $agendamento->email;
        }

        if ($agendamento->observacao_visitante) {
            $linhas[] = 'Observação: ' . trim($agendamento->observacao_visitante);
        }

        $linhas[] = '';
        $linhas[] = 'Protocolo: ' . $agendamento->protocolo;
        $linhas[] = '';
        $linhas[] = 'Aguardo confirmação, por favor.';

        return implode("\n", $linhas);
    }
}

@extends('console.layout')

@section('title', 'Detalhe do Agendamento')
@section('page.title', 'Detalhe do Agendamento')
@section('topbar.description', 'Visualize e opere um agendamento de espaço cultural dentro do shell global do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.espacos-culturais.index') }}" class="ui-console-topbar-tab">Espaços culturais</a>
  <a href="{{ route('coordenador.espacos-culturais.agendamentos.index') }}" class="ui-console-topbar-tab">Agendamentos</a>
  <span class="ui-console-topbar-tab is-active">Detalhe</span>
@endsection

@section('content')
<div class="ui-console-page">
  @if (session('ok'))
    <div class="ui-alert ui-alert-success mb-4">{{ session('ok') }}</div>
  @endif

  <x-dashboard.page-header
    title="Agendamento {{ $agendamento->protocolo }}"
    subtitle="{{ $agendamento->espaco?->nome }} • {{ ucfirst(str_replace('_', ' ', $agendamento->status)) }}"
  >
    <a href="{{ route('coordenador.espacos-culturais.agendamentos.index') }}" class="ui-btn-secondary">Voltar</a>
  </x-dashboard.page-header>

  <div class="ui-espaco-agendamento-grid mt-5">
    <div class="space-y-6 lg:col-span-2">
      <x-dashboard.section-card title="Dados da solicitação" subtitle="Dados do visitante e da visita" class="ui-coord-dashboard-panel">
        <div class="ui-espaco-detail-grid">
          <div><div class="ui-espaco-detail-label">Nome</div><div class="ui-espaco-detail-value">{{ $agendamento->nome }}</div></div>
          <div><div class="ui-espaco-detail-label">Telefone</div><div class="ui-espaco-detail-value">{{ $agendamento->telefone }}</div></div>
          <div><div class="ui-espaco-detail-label">E-mail</div><div class="ui-espaco-detail-value">{{ $agendamento->email ?: 'Não informado' }}</div></div>
          <div><div class="ui-espaco-detail-label">Quantidade</div><div class="ui-espaco-detail-value">{{ $agendamento->qtd_visitantes }}</div></div>
          <div><div class="ui-espaco-detail-label">Data da visita</div><div class="ui-espaco-detail-value">{{ optional($agendamento->data_visita)->format('d/m/Y') }}</div></div>
          <div><div class="ui-espaco-detail-label">Faixa</div><div class="ui-espaco-detail-value">{{ $agendamento->horario?->faixa_label ?: 'Não informada' }}</div></div>
        </div>

        @if ($agendamento->observacao_visitante)
          <div class="ui-espaco-note-card mt-6">
            <div class="ui-espaco-detail-label">Observação do visitante</div>
            <div class="mt-2 text-sm text-[var(--ui-text-soft)] whitespace-pre-line">{{ $agendamento->observacao_visitante }}</div>
          </div>
        @endif
      </x-dashboard.section-card>

      <x-dashboard.section-card title="Observação interna" subtitle="Anotações internas da equipe" class="ui-coord-dashboard-panel">
        <form action="{{ route('coordenador.espacos-culturais.agendamentos.observacao-interna', $agendamento) }}" method="POST" class="space-y-4">
          @csrf
          @method('PATCH')
          <textarea name="observacao_interna" rows="6" class="ui-form-control ui-aviso-textarea" placeholder="Anotações internas da equipe">{{ old('observacao_interna', $agendamento->observacao_interna) }}</textarea>
          <button type="submit" class="ui-btn-primary">Salvar observação</button>
        </form>
      </x-dashboard.section-card>
    </div>

    <div class="space-y-6">
      <x-dashboard.section-card title="Status e operação" subtitle="Atualize o andamento da solicitação" class="ui-coord-dashboard-panel">
        <div class="space-y-3">
          <form action="{{ route('coordenador.espacos-culturais.agendamentos.confirmar', $agendamento) }}" method="POST">
            @csrf
            @method('PATCH')
            <button type="submit" class="ui-btn-primary w-full">Confirmar</button>
          </form>

          <form action="{{ route('coordenador.espacos-culturais.agendamentos.concluir', $agendamento) }}" method="POST">
            @csrf
            @method('PATCH')
            <button type="submit" class="ui-btn-secondary w-full">Concluir</button>
          </form>

          <form action="{{ route('coordenador.espacos-culturais.agendamentos.cancelar', $agendamento) }}" method="POST" class="space-y-3">
            @csrf
            @method('PATCH')
            <textarea name="observacao_interna" rows="3" class="ui-form-control" placeholder="Motivo do cancelamento"></textarea>
            <button type="submit" class="ui-btn-danger w-full">Cancelar</button>
          </form>
        </div>
      </x-dashboard.section-card>

      <x-dashboard.section-card title="Atribuição de técnico" subtitle="Vincule o atendimento responsável" class="ui-coord-dashboard-panel">
        <form action="{{ route('coordenador.espacos-culturais.agendamentos.atribuir-tecnico', $agendamento) }}" method="POST" class="space-y-4">
          @csrf
          @method('PATCH')
          <select name="tecnico_id" class="ui-form-select">
            <option value="">Não atribuído</option>
            @foreach ($tecnicos as $tecnico)
              <option value="{{ $tecnico->id }}" @selected((string) old('tecnico_id', $agendamento->tecnico_id) === (string) $tecnico->id)>{{ $tecnico->name }}</option>
            @endforeach
          </select>
          <button type="submit" class="ui-btn-secondary w-full">Salvar atribuição</button>
        </form>
      </x-dashboard.section-card>

      <x-dashboard.section-card title="Resumo técnico" subtitle="Resumo operacional do atendimento" class="ui-coord-dashboard-panel">
        <div class="space-y-3 text-sm text-[var(--ui-text-soft)]">
          <div><div class="ui-espaco-detail-label">Espaço</div><div class="ui-espaco-detail-value">{{ $agendamento->espaco?->nome }}</div></div>
          <div><div class="ui-espaco-detail-label">Status</div><div class="ui-espaco-detail-value">{{ ucfirst(str_replace('_', ' ', $agendamento->status)) }}</div></div>
          <div><div class="ui-espaco-detail-label">Técnico</div><div class="ui-espaco-detail-value">{{ $agendamento->tecnico?->name ?: 'Não atribuído' }}</div></div>
          <div><div class="ui-espaco-detail-label">Atribuído por</div><div class="ui-espaco-detail-value">{{ $agendamento->atribuidor?->name ?: '—' }}</div></div>
          <div><div class="ui-espaco-detail-label">WhatsApp enviado</div><div class="ui-espaco-detail-value">{{ $agendamento->whatsapp_clicked_at ? $agendamento->whatsapp_clicked_at->format('d/m/Y H:i') : 'Ainda não' }}</div></div>

          @if ($agendamento->whatsapp_link)
            <a href="{{ localized_route('site.museus.agendamentos.whatsapp', ['protocolo' => $agendamento->protocolo]) }}" target="_blank" class="ui-btn-secondary mt-2">Abrir WhatsApp</a>
          @endif
        </div>
      </x-dashboard.section-card>
    </div>
  </div>
</div>
@endsection

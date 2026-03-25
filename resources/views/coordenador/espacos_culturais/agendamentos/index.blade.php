@extends('console.layout')

@section('title', 'Agendamentos de Espacos Culturais')
@section('page.title', 'Agendamentos de Espacos Culturais')
@section('topbar.description', 'Acompanhe, distribua e atualize solicitacoes de visita dentro do shell compartilhado do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.espacos-culturais.index') }}" class="ui-console-topbar-tab">Espacos culturais</a>
  <span class="ui-console-topbar-tab is-active">Agendamentos</span>
@endsection

@section('content')
<div class="ui-console-page">
  @if (session('ok'))
    <div class="ui-alert ui-alert-success mb-4">{{ session('ok') }}</div>
  @endif

  <x-dashboard.page-header
    title="Agendamentos"
    subtitle="Acompanhe, distribua e atualize solicitacoes de visita sem quebrar a estrutura do console."
  >
    <a href="{{ route('coordenador.espacos-culturais.index') }}" class="ui-btn-secondary">Voltar aos espacos</a>
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Filtros" subtitle="Refine por protocolo, visitante, espaco, tecnico, status e periodo" class="ui-coord-dashboard-panel mt-5">
    <form method="GET" class="grid grid-cols-1 gap-4 lg:grid-cols-6">
      <div class="lg:col-span-2">
        <label class="ui-form-label">Busca</label>
        <input type="text" name="q" value="{{ $q }}" class="ui-form-control" placeholder="Protocolo, nome, telefone, e-mail">
      </div>

      <div>
        <label class="ui-form-label">Status</label>
        <select name="status" class="ui-form-select">
          <option value="">Todos</option>
          @foreach (\App\Models\Catalogo\EspacoCulturalAgendamento::STATUSES as $item)
            <option value="{{ $item }}" @selected($status === $item)>{{ ucfirst(str_replace('_', ' ', $item)) }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="ui-form-label">Espaco</label>
        <select name="espaco_id" class="ui-form-select">
          <option value="">Todos</option>
          @foreach ($espacos as $espaco)
            <option value="{{ $espaco->id }}" @selected((string) $espacoId === (string) $espaco->id)>{{ $espaco->nome }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="ui-form-label">Tecnico</label>
        <select name="tecnico_id" class="ui-form-select">
          <option value="">Todos</option>
          @foreach ($tecnicos as $tecnico)
            <option value="{{ $tecnico->id }}" @selected((string) $tecnicoId === (string) $tecnico->id)>{{ $tecnico->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="ui-form-label">Data inicial</label>
        <input type="date" name="data_inicial" value="{{ $dataInicial }}" class="ui-form-control">
      </div>

      <div>
        <label class="ui-form-label">Data final</label>
        <input type="date" name="data_final" value="{{ $dataFinal }}" class="ui-form-control">
      </div>

      <div class="lg:col-span-6 flex flex-wrap gap-2">
        <button type="submit" class="ui-btn-primary">Filtrar</button>
        <a href="{{ route('coordenador.espacos-culturais.agendamentos.index') }}" class="ui-btn-secondary">Limpar</a>
      </div>
    </form>
  </x-dashboard.section-card>

  <x-dashboard.section-card title="Lista de agendamentos" subtitle="Acompanhe protocolos, visitante, espaco, data, status e tecnico" class="ui-coord-dashboard-panel mt-5">
    <div class="ui-table-shell">
      <table class="min-w-full">
        <thead class="ui-table-head">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Protocolo</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Visitante</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Espaco</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Data</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Status</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Tecnico</th>
            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide">Acao</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($agendamentos as $agendamento)
            <tr class="ui-table-row">
              <td class="px-4 py-4 text-sm font-medium text-[var(--ui-text-title)]">{{ $agendamento->protocolo }}</td>
              <td class="px-4 py-4 text-sm text-[var(--ui-text-soft)]">
                <div class="font-medium text-[var(--ui-text-title)]">{{ $agendamento->nome }}</div>
                <div>{{ $agendamento->telefone }}</div>
                @if ($agendamento->email)
                  <div class="text-[var(--ui-text-subtle)]">{{ $agendamento->email }}</div>
                @endif
              </td>
              <td class="px-4 py-4 text-sm text-[var(--ui-text-soft)]">
                <div class="font-medium text-[var(--ui-text-title)]">{{ $agendamento->espaco?->nome }}</div>
                @if ($agendamento->horario)
                  <div>{{ $agendamento->horario->faixa_label }}</div>
                @endif
              </td>
              <td class="px-4 py-4 text-sm text-[var(--ui-text-soft)]">{{ optional($agendamento->data_visita)->format('d/m/Y') }}</td>
              <td class="px-4 py-4 text-sm">
                @if($agendamento->status === 'confirmado')
                  <span class="ui-badge ui-badge-success">{{ ucfirst(str_replace('_', ' ', $agendamento->status)) }}</span>
                @elseif($agendamento->status === 'cancelado' || $agendamento->status === 'expirado')
                  <span class="ui-badge ui-badge-danger">{{ ucfirst(str_replace('_', ' ', $agendamento->status)) }}</span>
                @elseif($agendamento->status === 'concluido')
                  <span class="ui-badge ui-badge-primary">{{ ucfirst(str_replace('_', ' ', $agendamento->status)) }}</span>
                @else
                  <span class="ui-badge ui-badge-warning">{{ ucfirst(str_replace('_', ' ', $agendamento->status)) }}</span>
                @endif
              </td>
              <td class="px-4 py-4 text-sm text-[var(--ui-text-soft)]">{{ $agendamento->tecnico?->name ?: 'Nao atribuido' }}</td>
              <td class="px-4 py-4 text-right">
                <a href="{{ route('coordenador.espacos-culturais.agendamentos.show', $agendamento) }}" class="ui-btn-secondary">Abrir</a>
              </td>
            </tr>
          @empty
            <tr class="ui-table-row">
              <td colspan="7" class="px-4 py-12 text-center text-sm text-[var(--ui-text-soft)]">Nenhum agendamento encontrado.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $agendamentos->links() }}
    </div>
  </x-dashboard.section-card>
</div>
@endsection

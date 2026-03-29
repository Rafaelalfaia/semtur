@extends('console.layout')

@section('title', 'Espacos Culturais')
@section('page.title', 'Espacos Culturais')
@section('topbar.description', 'Gerencie museus e teatros com cadastro, midia e agendamentos dentro do shell compartilhado do console.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Espacos culturais</span>
  <a href="{{ route('coordenador.espacos-culturais.agendamentos.index') }}" class="ui-console-topbar-tab">Agendamentos</a>
  <a href="{{ route('coordenador.espacos-culturais.create') }}" class="ui-console-topbar-tab">Novo espaco</a>
@endsection

@section('content')
<div class="ui-console-page">
  @if (session('ok'))
    <div class="ui-alert ui-alert-success mb-4">{{ session('ok') }}</div>
  @endif

  @if (session('erro'))
    <div class="ui-alert ui-alert-danger mb-4">{{ session('erro') }}</div>
  @endif

  <x-dashboard.page-header
    title="Espacos culturais"
    subtitle="Gerencie museus e teatros com grade, midia e agendamentos em um painel mais leve e coerente com o novo console."
  >
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('coordenador.espacos-culturais.agendamentos.index') }}" class="ui-btn-secondary">Ver agendamentos</a>
      <a href="{{ route('coordenador.espacos-culturais.create') }}" class="ui-btn-primary">Novo espaco</a>
    </div>
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Filtros" subtitle="Busque por nome, resumo, bairro, status e tipo" class="ui-coord-dashboard-panel mt-5">
    <form method="GET" class="grid grid-cols-1 gap-4 lg:grid-cols-4">
      <div class="lg:col-span-2">
        <label class="ui-form-label">Buscar</label>
        <input type="text" name="busca" value="{{ $busca }}" class="ui-form-control" placeholder="Nome, resumo, bairro...">
      </div>

      <div>
        <label class="ui-form-label">Status</label>
        <select name="status" class="ui-form-select">
          <option value="todos" @selected($status === 'todos')>Todos</option>
          <option value="rascunho" @selected($status === 'rascunho')>Rascunho</option>
          <option value="publicado" @selected($status === 'publicado')>Publicado</option>
          <option value="arquivado" @selected($status === 'arquivado')>Arquivado</option>
        </select>
      </div>

      <div>
        <label class="ui-form-label">Tipo</label>
        <select name="tipo" class="ui-form-select">
          <option value="todos" @selected($tipo === 'todos')>Todos</option>
          <option value="museu" @selected($tipo === 'museu')>Museu</option>
          <option value="teatro" @selected($tipo === 'teatro')>Teatro</option>
        </select>
      </div>

      <div class="lg:col-span-4 flex flex-wrap gap-2">
        <button type="submit" class="ui-btn-primary">Filtrar</button>
        <a href="{{ route('coordenador.espacos-culturais.index') }}" class="ui-btn-secondary">Limpar</a>
      </div>
    </form>
  </x-dashboard.section-card>

  @if ($espacos->count())
    <div class="ui-espaco-card-grid mt-5">
      @foreach ($espacos as $espaco)
        <article class="ui-espaco-card">
          <div class="ui-espaco-card-media">
            @if ($espaco->capa_url)
              <img src="{{ $espaco->capa_url }}" alt="{{ $espaco->nome }}" class="h-full w-full object-cover">
            @else
              <div class="ui-espaco-card-media-empty">Sem capa</div>
            @endif
          </div>

          <div class="space-y-4 p-5">
            <div class="flex flex-wrap items-center gap-2">
              <span class="ui-badge ui-badge-primary">{{ $espaco->tipo_label }}</span>

              @if($espaco->status === 'publicado')
                <span class="ui-badge ui-badge-success">Publicado</span>
              @elseif($espaco->status === 'arquivado')
                <span class="ui-badge ui-badge-neutral">Arquivado</span>
              @else
                <span class="ui-badge ui-badge-warning">Rascunho</span>
              @endif
            </div>

            <div>
              <h2 class="text-lg font-semibold text-[var(--ui-text-title)]">{{ $espaco->nome }}</h2>
              @if ($espaco->resumo)
                <p class="mt-2 line-clamp-3 text-sm text-[var(--ui-text-soft)]">{{ $espaco->resumo }}</p>
              @endif
            </div>

            <div class="ui-espaco-stat-grid">
              <div class="ui-espaco-stat-card">
                <div class="ui-espaco-stat-label">Horarios</div>
                <div class="ui-espaco-stat-value">{{ $espaco->horarios_count }}</div>
              </div>

              <div class="ui-espaco-stat-card">
                <div class="ui-espaco-stat-label">Midias</div>
                <div class="ui-espaco-stat-value">{{ $espaco->midias_count }}</div>
              </div>

              <div class="ui-espaco-stat-card">
                <div class="ui-espaco-stat-label">Agendamentos</div>
                <div class="ui-espaco-stat-value">{{ $espaco->agendamentos_count }}</div>
              </div>

              <div class="ui-espaco-stat-card">
                <div class="ui-espaco-stat-label">Cidade</div>
                <div class="ui-espaco-stat-value">{{ $espaco->cidade ?: 'Altamira' }}</div>
              </div>
            </div>

            <div class="ui-espaco-card-actions">
              <a href="{{ route('coordenador.espacos-culturais.edit', $espaco) }}" class="ui-btn-primary">Editar</a>

              @if ($espaco->slug)
                <a href="{{ localized_route('site.museus.show', ['slug' => $espaco->slug]) }}" target="_blank" class="ui-btn-secondary">Ver site</a>
              @endif

              <a href="{{ route('coordenador.espacos-culturais.agendamentos.index', ['espaco_id' => $espaco->id]) }}" class="ui-btn-secondary">Agendamentos</a>
            </div>
          </div>
        </article>
      @endforeach
    </div>

    <div class="mt-6">
      {{ $espacos->links() }}
    </div>
  @else
    <x-dashboard.section-card title="Nenhum espaco cultural encontrado" subtitle="Ajuste os filtros ou cadastre o primeiro espaco." class="ui-coord-dashboard-panel mt-5">
      <a href="{{ route('coordenador.espacos-culturais.create') }}" class="ui-btn-primary mt-2">Cadastrar agora</a>
    </x-dashboard.section-card>
  @endif
</div>
@endsection

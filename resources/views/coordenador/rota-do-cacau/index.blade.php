@extends('console.layout')

@section('title', 'Rota do Cacau')
@section('page.title', 'Rota do Cacau')
@section('topbar.description', 'Gerencie o cadastro principal único da Rota do Cacau e o acesso às edições vinculadas.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Rota do Cacau</span>
  @if($rota)
    @can('rota_do_cacau.update')
      <a href="{{ route('coordenador.rota-do-cacau.edit', $rota) }}" class="ui-console-topbar-tab">Configuração</a>
    @endcan
    @can('rota_do_cacau.edicoes.view')
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-console-topbar-tab">Edições</a>
    @endcan
  @elseif(Route::has('coordenador.rota-do-cacau.create'))
    @can('rota_do_cacau.create')
      <a href="{{ route('coordenador.rota-do-cacau.create') }}" class="ui-console-topbar-tab">Criar configuração</a>
    @endcan
  @endif
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Rota do Cacau"
    subtitle="O módulo trabalha com um único cadastro institucional principal e várias edições vinculadas por ano."
  >
    @if($rota)
      <div class="flex flex-wrap items-center gap-3">
        @can('rota_do_cacau.update')
          <a href="{{ route('coordenador.rota-do-cacau.edit', $rota) }}" class="ui-btn-secondary">Editar configuração</a>
        @endcan
        @can('rota_do_cacau.edicoes.view')
          <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-btn-primary">Gerenciar edições</a>
        @endcan
      </div>
    @elseif(Route::has('coordenador.rota-do-cacau.create'))
      @can('rota_do_cacau.create')
        <a href="{{ route('coordenador.rota-do-cacau.create') }}" class="ui-btn-primary">Criar primeiro registro</a>
      @endcan
    @endif
  </x-dashboard.page-header>

  @if(($registrosExtras ?? 0) > 0)
    <div class="ui-alert ui-alert-warning mt-5">
      Existem {{ $registrosExtras }} registro(s) extra(s) na base. O painel está operando apenas sobre o primeiro cadastro principal, sem alterar nem remover os demais.
    </div>
  @endif

  @if($rota)
    <x-dashboard.section-card title="Cadastro principal" subtitle="Configuração institucional única da Rota do Cacau" class="ui-coord-dashboard-panel mt-5">
      <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-5">
          <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
              <div class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--ui-text-soft)]">Status</div>
              <div class="mt-3">
                @if($rota->status === 'publicado')
                  <span class="ui-badge ui-badge-success">Publicado</span>
                @elseif($rota->status === 'arquivado')
                  <span class="ui-badge ui-badge-warning">Arquivado</span>
                @else
                  <span class="ui-badge ui-badge-neutral">Rascunho</span>
                @endif
              </div>
            </div>
            <div class="rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
              <div class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--ui-text-soft)]">Slug</div>
              <div class="mt-3 text-sm font-semibold text-[var(--ui-text-title)]">/{{ $rota->slug }}</div>
            </div>
            <div class="rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
              <div class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--ui-text-soft)]">Ordem</div>
              <div class="mt-3 text-sm font-semibold text-[var(--ui-text-title)]">{{ $rota->ordem }}</div>
            </div>
            <div class="rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
              <div class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--ui-text-soft)]">Edições</div>
              <div class="mt-3 text-sm font-semibold text-[var(--ui-text-title)]">{{ $rota->edicoes_count }}</div>
            </div>
          </div>

          <div class="rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
              <div class="min-w-0">
                <h2 class="text-lg font-semibold text-[var(--ui-text-title)]">{{ $rota->titulo }}</h2>
                <p class="mt-1 text-sm text-[var(--ui-text-soft)]">
                  Publicado em {{ optional($rota->published_at)->format('d/m/Y H:i') ?: 'não definido' }}
                </p>
              </div>
              <div class="flex flex-wrap gap-2">
                @can('rota_do_cacau.update')
                  <a href="{{ route('coordenador.rota-do-cacau.edit', $rota) }}" class="ui-btn-secondary">Editar configuração</a>
                @endcan
                @can('rota_do_cacau.edicoes.view')
                  <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-btn-primary">Abrir edições</a>
                @endcan
              </div>
            </div>

            <div class="mt-4 text-sm leading-7 text-[var(--ui-text-soft)]">
              {{ $rota->descricao ?: 'Nenhuma descrição institucional cadastrada até o momento.' }}
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div class="rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
            <div class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--ui-text-soft)]">Foto de perfil</div>
            @if($rota->foto_perfil_url)
              <img src="{{ $rota->foto_perfil_url }}" alt="" class="mt-3 h-48 w-full rounded-3xl object-cover border border-[var(--ui-border)]">
            @else
              <div class="mt-3 flex h-48 w-full items-center justify-center rounded-3xl border border-dashed border-[var(--ui-border)] bg-[var(--ui-surface)] text-sm text-[var(--ui-text-soft)]">
                Sem foto de perfil
              </div>
            @endif
          </div>

          <div class="rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
            <div class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--ui-text-soft)]">Foto de capa</div>
            @if($rota->foto_capa_url)
              <img src="{{ $rota->foto_capa_url }}" alt="" class="mt-3 h-48 w-full rounded-3xl object-cover border border-[var(--ui-border)]">
            @else
              <div class="mt-3 flex h-48 w-full items-center justify-center rounded-3xl border border-dashed border-[var(--ui-border)] bg-[var(--ui-surface)] text-sm text-[var(--ui-text-soft)]">
                Sem foto de capa
              </div>
            @endif
          </div>
        </div>
      </div>
    </x-dashboard.section-card>
  @else
    <x-dashboard.section-card title="Cadastro principal" subtitle="Crie o primeiro registro institucional do módulo para depois gerenciar as edições." class="ui-coord-dashboard-panel mt-5">
      <div class="rounded-3xl border border-dashed border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-8 text-center">
        <h2 class="text-lg font-semibold text-[var(--ui-text-title)]">Nenhum cadastro principal encontrado</h2>
        <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-[var(--ui-text-soft)]">
          O módulo de Rota do Cacau trabalha com um único registro principal. Crie esse cadastro uma única vez e depois utilize o fluxo de edições normalmente.
        </p>
        @if(Route::has('coordenador.rota-do-cacau.create'))
          @can('rota_do_cacau.create')
            <a href="{{ route('coordenador.rota-do-cacau.create') }}" class="ui-btn-primary mt-5 inline-flex">Criar primeiro registro</a>
          @endcan
        @endif
      </div>
    </x-dashboard.section-card>
  @endif
</div>
@endsection

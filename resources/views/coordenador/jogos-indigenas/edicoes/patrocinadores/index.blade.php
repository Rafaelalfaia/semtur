@extends('console.layout')

@section('title', 'Patrocinadores - '.$edicao->titulo)
@section('page.title', 'Patrocinadores da edicao')
@section('topbar.description', 'Gerencie os patrocinadores vinculados a esta edicao no mesmo padrao do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indigenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edicoes</a>
  <span class="ui-console-topbar-tab is-active">Patrocinadores</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Patrocinadores da edicao" subtitle="Cadastre logo, URL e ordem de exibicao para os apoiadores desta edicao.">
    <x-slot:actions>
      <div class="flex flex-wrap gap-2">
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.create', [$jogo, $edicao]) }}" class="ui-btn-primary">Novo patrocinador</a>
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.edit', [$jogo, $edicao]) }}" class="ui-btn-secondary">Voltar a edicao</a>
      </div>
    </x-slot:actions>
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Lista de patrocinadores" subtitle="Os patrocinadores pertencem exclusivamente a esta edicao." class="ui-coord-dashboard-panel mt-5">
    @if($patrocinadores->count())
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($patrocinadores as $patrocinador)
          <article class="rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-4">
            <div class="flex items-start gap-4">
              <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)]">
                @if($patrocinador->logo_url)
                  <img src="{{ $patrocinador->logo_url }}" alt="{{ $patrocinador->nome }}" class="h-full w-full object-contain">
                @else
                  <span class="text-xs text-[var(--ui-text-soft)]">Sem logo</span>
                @endif
              </div>
              <div class="min-w-0 flex-1">
                <h2 class="text-base font-semibold text-[var(--ui-text-title)]">{{ $patrocinador->nome }}</h2>
                <p class="mt-1 text-sm text-[var(--ui-text-soft)]">Ordem {{ $patrocinador->ordem }}</p>
                @if($patrocinador->url)
                  <a href="{{ $patrocinador->url }}" target="_blank" rel="noreferrer" class="mt-2 inline-flex text-sm text-[var(--ui-accent)] hover:underline">Abrir link</a>
                @endif
              </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
              <a href="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.edit', [$jogo, $edicao, $patrocinador]) }}" class="ui-btn-secondary">Editar</a>
              <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.destroy', [$jogo, $edicao, $patrocinador]) }}" onsubmit="return confirm('Excluir este patrocinador?');">
                @csrf
                @method('DELETE')
                <button class="ui-btn-danger">Excluir</button>
              </form>
            </div>
          </article>
        @endforeach
      </div>
      <div class="mt-4">{{ $patrocinadores->links() }}</div>
    @else
      <div class="rounded-3xl border border-dashed border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-8 text-center">
        <h2 class="text-lg font-semibold text-[var(--ui-text-title)]">Nenhum patrocinador cadastrado</h2>
        <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-[var(--ui-text-soft)]">
          Cadastre os apoiadores desta edicao com opcao de logo, link e ordem de exibicao.
        </p>
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.create', [$jogo, $edicao]) }}" class="ui-btn-primary mt-5 inline-flex">Adicionar patrocinador</a>
      </div>
    @endif
  </x-dashboard.section-card>
</div>
@endsection

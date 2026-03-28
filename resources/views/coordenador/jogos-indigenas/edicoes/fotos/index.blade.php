@extends('console.layout')

@section('title', 'Fotos - '.$edicao->titulo)
@section('page.title', 'Galeria da edição')
@section('topbar.description', 'Gerencie as fotos da edição dentro do padrão visual do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indígenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edições</a>
  <span class="ui-console-topbar-tab is-active">Fotos</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Fotos da edição"
    subtitle="Adicione imagens, legendas e ordem para compor a galeria do ano {{ $edicao->ano }}."
  >
    <x-slot:actions>
      <div class="flex flex-wrap gap-2">
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.fotos.create', [$jogo, $edicao]) }}" class="ui-btn-primary">Nova foto</a>
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.edit', [$jogo, $edicao]) }}" class="ui-btn-secondary">Voltar à edição</a>
      </div>
    </x-slot:actions>
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Galeria" subtitle="As fotos pertencem exclusivamente a esta edição." class="ui-coord-dashboard-panel mt-5">
    @if($fotos->count())
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($fotos as $foto)
          <article class="overflow-hidden rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface)]">
            <img src="{{ $foto->imagem_url }}" alt="{{ $foto->legenda ?: 'Foto da edição' }}" class="h-56 w-full object-cover">
            <div class="space-y-3 p-4">
              <div>
                <div class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--ui-text-soft)]">Ordem {{ $foto->ordem }}</div>
                <div class="mt-2 text-sm text-[var(--ui-text)]">{{ $foto->legenda ?: 'Sem legenda cadastrada.' }}</div>
              </div>
              <div class="flex flex-wrap gap-2">
                <a href="{{ route('coordenador.jogos-indigenas.edicoes.fotos.edit', [$jogo, $edicao, $foto]) }}" class="ui-btn-secondary">Editar</a>
                <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.fotos.destroy', [$jogo, $edicao, $foto]) }}" onsubmit="return confirm('Excluir esta foto da galeria?');">
                  @csrf
                  @method('DELETE')
                  <button class="ui-btn-danger">Excluir</button>
                </form>
              </div>
            </div>
          </article>
        @endforeach
      </div>
      <div class="mt-4">{{ $fotos->links() }}</div>
    @else
      <div class="rounded-3xl border border-dashed border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-8 text-center">
        <h2 class="text-lg font-semibold text-[var(--ui-text-title)]">Nenhuma foto cadastrada</h2>
        <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-[var(--ui-text-soft)]">
          Use a galeria desta edição para organizar o conteúdo visual do ano sem misturar com o cadastro principal institucional.
        </p>
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.fotos.create', [$jogo, $edicao]) }}" class="ui-btn-primary mt-5 inline-flex">Adicionar primeira foto</a>
      </div>
    @endif
  </x-dashboard.section-card>
</div>
@endsection

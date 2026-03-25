@extends('console.layout')

@section('title', 'Fotos - '.$edicao->titulo)
@section('page.title', 'Galeria da edicao')
@section('topbar.description', 'Gerencie as fotos da edicao dentro do padrao visual do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-console-topbar-tab">Rota do Cacau</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-console-topbar-tab">Edicoes</a>
  <span class="ui-console-topbar-tab is-active">Fotos</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Fotos da edicao"
    subtitle="Adicione imagens, legendas e ordem para compor a galeria do ano {{ $edicao->ano }}."
  >
    <x-slot:actions>
      <div class="flex flex-wrap gap-2">
        @can('rota_do_cacau.edicoes.fotos.create')
          <a href="{{ route('coordenador.rota-do-cacau.edicoes.fotos.create', [$rota, $edicao]) }}" class="ui-btn-primary">Nova foto</a>
        @endcan
        <a href="{{ route('coordenador.rota-do-cacau.edicoes.edit', [$rota, $edicao]) }}" class="ui-btn-secondary">Voltar a edicao</a>
      </div>
    </x-slot:actions>
  </x-dashboard.page-header>

  <div class="mt-2 text-sm text-[var(--ui-text-soft)]">Rota do Cacau > Edicoes > {{ $edicao->titulo }} > Fotos</div>

  <x-dashboard.section-card title="Galeria" subtitle="As fotos pertencem exclusivamente a esta edicao." class="ui-coord-dashboard-panel mt-5">
    @if($fotos->count())
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($fotos as $foto)
          <article class="overflow-hidden rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface)]">
            <img src="{{ $foto->imagem_url }}" alt="{{ $foto->legenda ?: 'Foto da edicao' }}" class="h-56 w-full object-cover">
            <div class="space-y-3 p-4">
              <div>
                <div class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--ui-text-soft)]">Ordem {{ $foto->ordem }}</div>
                <div class="mt-2 text-sm text-[var(--ui-text)]">{{ $foto->legenda ?: 'Sem legenda cadastrada.' }}</div>
              </div>
              @canany(['rota_do_cacau.edicoes.fotos.update', 'rota_do_cacau.edicoes.fotos.delete'])
                <div class="flex flex-wrap gap-2">
                  @can('rota_do_cacau.edicoes.fotos.update')
                    <a href="{{ route('coordenador.rota-do-cacau.edicoes.fotos.edit', [$rota, $edicao, $foto]) }}" class="ui-btn-secondary">Editar</a>
                  @endcan
                  @can('rota_do_cacau.edicoes.fotos.delete')
                    <form method="POST" action="{{ route('coordenador.rota-do-cacau.edicoes.fotos.destroy', [$rota, $edicao, $foto]) }}" onsubmit="return confirm('Excluir esta foto da galeria?');">
                      @csrf
                      @method('DELETE')
                      <button class="ui-btn-danger">Excluir</button>
                    </form>
                  @endcan
                </div>
              @endcanany
            </div>
          </article>
        @endforeach
      </div>
      <div class="mt-4">{{ $fotos->links() }}</div>
    @else
      <div class="rounded-3xl border border-dashed border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-8 text-center">
        <h2 class="text-lg font-semibold text-[var(--ui-text-title)]">Nenhuma foto cadastrada</h2>
        <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-[var(--ui-text-soft)]">
          Use a galeria desta edicao para organizar o conteudo visual do ano sem misturar com o cadastro principal institucional.
        </p>
        @can('rota_do_cacau.edicoes.fotos.create')
          <a href="{{ route('coordenador.rota-do-cacau.edicoes.fotos.create', [$rota, $edicao]) }}" class="ui-btn-primary mt-5 inline-flex">Adicionar primeira foto</a>
        @endcan
      </div>
    @endif
  </x-dashboard.section-card>
</div>
@endsection

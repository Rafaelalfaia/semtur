@extends('console.layout')
@section('title', 'Galeria - '.$edicao->evento->nome.' ('.$edicao->ano.')')
@section('page.title', 'Galeria')
@section('topbar.description', 'Gerencie a galeria da edição com upload, ordenação e remoção no mesmo padrão visual do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.eventos.index') }}" class="ui-console-topbar-tab">Eventos</a>
  <a href="{{ route('coordenador.eventos.edicoes.index', $edicao->evento) }}" class="ui-console-topbar-tab">Edições</a>
  <span class="ui-console-topbar-tab is-active">Galeria</span>
@endsection

@section('content')
@php use Illuminate\Support\Facades\Storage; @endphp

<div class="ui-console-page">
  <x-dashboard.page-header
    title="Galeria"
    subtitle="Envie, remova e reorganize fotos da edição sem quebrar a estrutura global do console."
  >
    <a href="{{ route('coordenador.eventos.edicoes.index', $edicao->evento) }}" class="ui-btn-secondary">Voltar às edições</a>
  </x-dashboard.page-header>

  @if(session('ok'))
    <div class="ui-alert ui-alert-success mt-5">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="ui-alert ui-alert-danger mt-5">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <x-dashboard.section-card title="Adicionar fotos" subtitle="Envie múltiplas imagens para a galeria da edição" class="ui-coord-dashboard-panel mt-5">
    <form method="POST" action="{{ route('coordenador.edicoes.midias.store', $edicao) }}" enctype="multipart/form-data" class="space-y-3">
      @csrf
      <label class="ui-form-label">Adicionar fotos (múltiplas)</label>
      <input type="file" name="fotos[]" accept="image/*" multiple class="ui-form-control">
      <button class="ui-btn-primary">Enviar</button>
    </form>
  </x-dashboard.section-card>

  <x-dashboard.section-card title="Midias da galeria" subtitle="Acompanhe a ordem e exclua itens quando necessario" class="ui-coord-dashboard-panel mt-5">
    <div class="ui-event-media-grid">
      @forelse($midias as $m)
        <article class="ui-event-media-card">
          <img src="{{ Storage::disk('public')->url($m->path) }}" class="ui-event-media-image" alt="{{ $m->alt }}">
          <div class="ui-event-media-meta">
            <span class="text-xs text-[var(--ui-text-soft)]">#{{ $m->ordem }}</span>
            <form method="POST" action="{{ route('coordenador.midias.destroy', $m) }}" onsubmit="return confirm('Excluir imagem?');">
              @csrf
              @method('DELETE')
              <button class="ui-btn-danger">Excluir</button>
            </form>
          </div>
        </article>
      @empty
        <div class="col-span-full py-10 text-center text-[var(--ui-text-soft)]">Nenhuma foto enviada.</div>
      @endforelse
    </div>

    <div class="mt-4">{{ $midias->links() }}</div>

    <form method="POST" action="{{ route('coordenador.edicoes.midias.reordenar', $edicao) }}" class="ui-event-inline-form mt-4">
      @csrf
      <input name="ordem" placeholder='{"33":1,"41":2}' class="ui-form-control">
      <button class="ui-btn-secondary">Aplicar ordem</button>
    </form>
  </x-dashboard.section-card>
</div>
@endsection

@extends('console.layout')

@section('title', 'Editar Espaco Cultural')
@section('page.title', 'Editar Espaco Cultural')
@section('topbar.description', 'Atualize um espaco cultural mantendo compatibilidade total com o shell, o modo global e a futura base de temas.')

@section('topbar.nav')
  <a href="{{ route('coordenador.espacos-culturais.index') }}" class="ui-console-topbar-tab">Espacos culturais</a>
  <span class="ui-console-topbar-tab is-active">Editar espaco</span>
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
    title="{{ $espaco->nome ?: 'Editar espaco cultural' }}"
    subtitle="{{ $espaco->tipo_label }} • status atual: {{ ucfirst($espaco->status ?? 'rascunho') }}"
  >
    <div class="flex flex-wrap gap-2">
      @if ($espaco->slug)
        <a href="{{ localized_route('site.museus.show', ['slug' => $espaco->slug]) }}" target="_blank" class="ui-btn-secondary">Ver no site</a>
      @endif
      <a href="{{ route('coordenador.espacos-culturais.index') }}" class="ui-btn-secondary">Voltar</a>
    </div>
  </x-dashboard.page-header>

  <form action="{{ route('coordenador.espacos-culturais.update', $espaco) }}" method="POST" enctype="multipart/form-data" class="mt-5">
    @csrf
    @method('PUT')
    @include('coordenador.espacos_culturais._form')
  </form>

  <x-dashboard.section-card title="Zona de cuidado" subtitle="A exclusao e bloqueada quando existirem agendamentos futuros ativos." class="ui-coord-dashboard-panel mt-8 ui-espaco-danger-zone">
    <form action="{{ route('coordenador.espacos-culturais.destroy', $espaco) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja arquivar este espaco cultural?');">
      @csrf
      @method('DELETE')
      <button type="submit" class="ui-btn-danger">Arquivar espaco cultural</button>
    </form>
  </x-dashboard.section-card>
</div>
@endsection

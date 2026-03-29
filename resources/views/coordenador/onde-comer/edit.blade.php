@extends('console.layout')

@section('title', 'Onde comer')
@section('page.title', 'Onde comer')
@section('topbar.description', 'Gerencie a página editorial de gastronomia com o mesmo shell e fluxo visual do console.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Onde comer</span>
@endsection

@section('content')
<div class="ui-console-page">
    @include('coordenador.partials.flash')

    <x-dashboard.page-header
        title="Onde comer"
        subtitle="Edite os textos editoriais e selecione manualmente as empresas gastronômicas que aparecem no site."
    >
        @if(($pagina->status ?? null) === 'publicado')
            <a href="{{ route('site.onde_comer') }}" target="_blank" class="ui-btn-secondary">Ver no site</a>
        @endif
    </x-dashboard.page-header>

    <form
        method="POST"
        action="{{ route('coordenador.onde_comer.update') }}"
        enctype="multipart/form-data"
        class="mt-5 space-y-6"
    >
        @csrf
        @method('PUT')

        @include('coordenador.onde-comer._form')

        <div class="flex flex-wrap items-center gap-3 border-t border-white/10 pt-5">
            <button type="submit" class="ui-btn-primary">Salvar página</button>
        </div>
    </form>
</div>
@endsection

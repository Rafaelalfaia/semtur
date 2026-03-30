@extends('console.layout')

@section('title', 'Editar idioma - '.($idioma->nome ?? 'Idioma'))

@section('topbar.description', 'Edição administrativa da base de idiomas do sistema, ainda isolada do frontend público nesta etapa.')

@section('topbar.nav')
    <a href="{{ route('admin.idiomas.index') }}" class="ui-console-topbar-tab">Idiomas</a>
    <span class="ui-console-topbar-tab is-active">Editar</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Editar idioma"
        subtitle="Ajuste os dados da base nova sem alterar ainda a lógica pública de locale."
    >
        <x-slot:actions>
            <a href="{{ route('admin.idiomas.index') }}" class="ui-btn-secondary">Voltar</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <form method="POST" action="{{ route('admin.idiomas.update', $idioma) }}" class="mt-5 space-y-4">
        @csrf
        @method('PUT')

        @include('admin.idiomas._form', ['idioma' => $idioma])

        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('admin.idiomas.index') }}" class="ui-btn-secondary">Voltar</a>

            <button type="submit" class="ui-btn-primary">Salvar alterações</button>
        </div>
    </form>
</div>
@endsection

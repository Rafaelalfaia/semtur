@extends('console.layout')

@section('title', 'Novo idioma')

@section('topbar.description', 'Cadastro administrativo de idiomas para substituir a configuração fixa atual em etapas seguras.')

@section('topbar.nav')
    <a href="{{ route('admin.idiomas.index') }}" class="ui-console-topbar-tab">Idiomas</a>
    <span class="ui-console-topbar-tab is-active">Novo idioma</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Novo idioma"
        subtitle="Cadastre a base do idioma com nome, sigla, bandeira e metadados mínimos de sistema."
    >
        <x-slot:actions>
            <a href="{{ route('admin.idiomas.index') }}" class="ui-btn-secondary">Voltar</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <form method="POST" action="{{ route('admin.idiomas.store') }}" class="mt-5 space-y-4">
        @csrf

        @include('admin.idiomas._form', ['idioma' => $idioma])

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.idiomas.index') }}" class="ui-btn-secondary">Cancelar</a>
            <button type="submit" class="ui-btn-primary">Salvar</button>
        </div>
    </form>
</div>
@endsection

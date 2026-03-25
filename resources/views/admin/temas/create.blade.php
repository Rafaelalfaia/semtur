@extends('console.layout')

@section('title', 'Novo tema')

@section('topbar.description', 'Crie um tema administrativo organizado, com tokens, assets e fallback institucional desde a origem.')

@section('topbar.nav')
    <a href="{{ route('admin.temas.index') }}" class="ui-console-topbar-tab">Temas</a>
    <span class="ui-console-topbar-tab is-active">Novo tema</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Novo tema"
        subtitle="Cadastre a identidade visual do tema sem recorrer a JSON cru como interface principal."
    >
        <x-slot:actions>
            <a href="{{ route('admin.temas.index') }}" class="ui-btn-secondary">Voltar</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <form method="POST" action="{{ route('admin.temas.store') }}" enctype="multipart/form-data" class="mt-5 space-y-5">
        @csrf
        @include('admin.temas._form', ['mode' => 'create'])

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('admin.temas.index') }}" class="ui-btn-secondary">Cancelar</a>
            <button class="ui-btn-primary">Salvar tema</button>
        </div>
    </form>
</div>
@endsection

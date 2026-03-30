@extends('console.layout')

@section('title', 'Nova tradução')

@section('topbar.description', 'Cadastro administrativo de chaves traduzíveis, sem substituir ainda o frontend público.')

@section('topbar.nav')
    <a href="{{ route('admin.traducoes.index') }}" class="ui-console-topbar-tab">Traduções</a>
    <span class="ui-console-topbar-tab is-active">Nova chave</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Nova chave de tradução"
        subtitle="Cadastre o texto base e os valores por idioma para iniciar o catálogo administrativo de traduções."
    >
        <x-slot:actions>
            <a href="{{ route('admin.traducoes.index') }}" class="ui-btn-secondary">Voltar</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <form method="POST" action="{{ route('admin.traducoes.store') }}" class="mt-5 space-y-4">
        @csrf

        @include('admin.traducoes._form', ['translation' => $translation, 'idiomas' => $idiomas, 'translationValues' => $translationValues])

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.traducoes.index') }}" class="ui-btn-secondary">Cancelar</a>
            <button type="submit" class="ui-btn-primary">Salvar</button>
        </div>
    </form>
</div>
@endsection

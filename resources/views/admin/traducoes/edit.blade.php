@extends('console.layout')

@section('title', 'Editar tradução - '.($translation->key ?? 'Tradução'))

@section('topbar.description', 'Edição administrativa do catálogo de traduções, ainda sem alterar a leitura pública do site.')

@section('topbar.nav')
    <a href="{{ route('admin.traducoes.index') }}" class="ui-console-topbar-tab">Traduções</a>
    <span class="ui-console-topbar-tab is-active">Editar</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Editar chave de tradução"
        subtitle="Ajuste texto base e valores por idioma antes da futura conexão com o frontend."
    >
        <x-slot:actions>
            <a href="{{ route('admin.traducoes.index') }}" class="ui-btn-secondary">Voltar</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <form method="POST" action="{{ route('admin.traducoes.update', ['translation' => $translation]) }}" class="mt-5 space-y-4">
        @csrf
        @method('PUT')

        @include('admin.traducoes._form', ['translation' => $translation, 'idiomas' => $idiomas, 'translationValues' => $translationValues])

        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('admin.traducoes.index') }}" class="ui-btn-secondary">Voltar</a>
            <button type="submit" class="ui-btn-primary">Salvar alterações</button>
        </div>
    </form>
</div>
@endsection

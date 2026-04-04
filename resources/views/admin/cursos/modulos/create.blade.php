@extends('console.layout')

@section('title', 'Novo módulo - '.$curso->nome)

@section('topbar.description', 'Cadastro administrativo de módulo dentro do curso selecionado, ainda sem sair do dashboard.')

@section('topbar.nav')
    <a href="{{ route('admin.cursos.index') }}" class="ui-console-topbar-tab">Cursos</a>
    <a href="{{ route('admin.cursos.modulos.index', $curso) }}" class="ui-console-topbar-tab">Módulos</a>
    <span class="ui-console-topbar-tab is-active">Novo módulo</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Novo módulo"
        subtitle="Crie a próxima camada da estrutura do curso com nome, capa e estado editorial."
    >
        <x-slot:actions>
            <a href="{{ route('admin.cursos.modulos.index', $curso) }}" class="ui-btn-secondary">Voltar</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <form method="POST" action="{{ route('admin.cursos.modulos.store', $curso) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
        @csrf
        @include('admin.cursos.modulos._form', ['curso' => $curso, 'modulo' => $modulo, 'statuses' => $statuses, 'mode' => 'create'])
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.cursos.modulos.index', $curso) }}" class="ui-btn-secondary">Cancelar</a>
            <button type="submit" class="ui-btn-primary">Salvar</button>
        </div>
    </form>
</div>
@endsection

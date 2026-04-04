@extends('console.layout')

@section('title', 'Nova aula - '.$modulo->nome)

@section('topbar.description', 'Cadastro administrativo da aula dentro do módulo atual, sem sair do dashboard.')

@section('topbar.nav')
    <a href="{{ route('admin.cursos.index') }}" class="ui-console-topbar-tab">Cursos</a>
    <a href="{{ route('admin.cursos.modulos.index', $curso) }}" class="ui-console-topbar-tab">Módulos</a>
    <a href="{{ route('admin.cursos.modulos.aulas.index', [$curso, $modulo]) }}" class="ui-console-topbar-tab">Aulas</a>
    <span class="ui-console-topbar-tab is-active">Nova aula</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Nova aula"
        subtitle="Crie a aula com nome, descrição, capa e link do vídeo no Google Drive."
    >
        <x-slot:actions>
            <a href="{{ route('admin.cursos.modulos.aulas.index', [$curso, $modulo]) }}" class="ui-btn-secondary">Voltar</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <form method="POST" action="{{ route('admin.cursos.modulos.aulas.store', [$curso, $modulo]) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
        @csrf
        @include('admin.cursos.aulas._form', ['curso' => $curso, 'modulo' => $modulo, 'aula' => $aula, 'statuses' => $statuses, 'mode' => 'create'])
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.cursos.modulos.aulas.index', [$curso, $modulo]) }}" class="ui-btn-secondary">Cancelar</a>
            <button type="submit" class="ui-btn-primary">Salvar</button>
        </div>
    </form>
</div>
@endsection

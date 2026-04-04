@extends('console.layout')

@section('title', 'Editar aula - '.($aula->nome ?? 'Aula'))

@section('topbar.description', 'Edição administrativa da aula atual, preservando a hierarquia curso, módulo e aula no console.')

@section('topbar.nav')
    <a href="{{ route('admin.cursos.index') }}" class="ui-console-topbar-tab">Cursos</a>
    <a href="{{ route('admin.cursos.modulos.index', $curso) }}" class="ui-console-topbar-tab">Módulos</a>
    <a href="{{ route('admin.cursos.modulos.aulas.index', [$curso, $modulo]) }}" class="ui-console-topbar-tab">Aulas</a>
    <span class="ui-console-topbar-tab is-active">Editar</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Editar aula"
        subtitle="Ajuste os dados da aula e valide o link do vídeo antes de publicar."
    >
        <x-slot:actions>
            <a href="{{ route('admin.cursos.modulos.aulas.index', [$curso, $modulo]) }}" class="ui-btn-secondary">Voltar</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <form method="POST" action="{{ route('admin.cursos.modulos.aulas.update', [$curso, $modulo, $aula]) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
        @csrf
        @method('PUT')
        @include('admin.cursos.aulas._form', ['curso' => $curso, 'modulo' => $modulo, 'aula' => $aula, 'statuses' => $statuses, 'mode' => 'edit'])
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('admin.cursos.modulos.aulas.index', [$curso, $modulo]) }}" class="ui-btn-secondary">Voltar</a>
            <button type="submit" class="ui-btn-primary">Salvar alterações</button>
        </div>
    </form>
</div>
@endsection

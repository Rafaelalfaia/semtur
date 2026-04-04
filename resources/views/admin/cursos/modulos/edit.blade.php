@extends('console.layout')

@section('title', 'Editar módulo - '.($modulo->nome ?? 'Módulo'))

@section('topbar.description', 'Edição administrativa do módulo atual, preservando a hierarquia interna do curso.')

@section('topbar.nav')
    <a href="{{ route('admin.cursos.index') }}" class="ui-console-topbar-tab">Cursos</a>
    <a href="{{ route('admin.cursos.modulos.index', $curso) }}" class="ui-console-topbar-tab">Módulos</a>
    <span class="ui-console-topbar-tab is-active">Editar</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Editar módulo"
        subtitle="Ajuste os dados do módulo e prepare o terreno para a camada de aulas."
    >
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.cursos.modulos.aulas.index', [$curso, $modulo]) }}" class="ui-btn-primary">Aulas</a>
                <a href="{{ route('admin.cursos.modulos.index', $curso) }}" class="ui-btn-secondary">Voltar</a>
            </div>
        </x-slot:actions>
    </x-dashboard.page-header>

    <form method="POST" action="{{ route('admin.cursos.modulos.update', [$curso, $modulo]) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
        @csrf
        @method('PUT')
        @include('admin.cursos.modulos._form', ['curso' => $curso, 'modulo' => $modulo, 'statuses' => $statuses, 'mode' => 'edit'])
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('admin.cursos.modulos.index', $curso) }}" class="ui-btn-secondary">Voltar</a>
            <button type="submit" class="ui-btn-primary">Salvar alterações</button>
        </div>
    </form>
</div>
@endsection

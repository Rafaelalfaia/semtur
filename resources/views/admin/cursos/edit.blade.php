@extends('console.layout')

@section('title', 'Editar curso - '.($curso->nome ?? 'Curso'))

@section('topbar.description', 'Edição administrativa do curso base, preservando a estrutura do dashboard para a próxima fase de módulos.')

@section('topbar.nav')
    <a href="{{ route('admin.cursos.index') }}" class="ui-console-topbar-tab">Cursos</a>
    <span class="ui-console-topbar-tab is-active">Editar</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Editar curso"
        subtitle="Ajuste nome, capa, ordem e status do curso base sem sair do shell administrativo."
    >
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.cursos.modulos.index', $curso) }}" class="ui-btn-primary">Módulos</a>
                <a href="{{ route('admin.cursos.index') }}" class="ui-btn-secondary">Voltar</a>
            </div>
        </x-slot:actions>
    </x-dashboard.page-header>

    <form method="POST" action="{{ route('admin.cursos.update', $curso) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
        @csrf
        @method('PUT')

        @include('admin.cursos._form', ['curso' => $curso, 'statuses' => $statuses, 'publicosAlvo' => $publicosAlvo, 'mode' => 'edit'])

        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('admin.cursos.index') }}" class="ui-btn-secondary">Voltar</a>
            <button type="submit" class="ui-btn-primary">Salvar alterações</button>
        </div>
    </form>
</div>
@endsection

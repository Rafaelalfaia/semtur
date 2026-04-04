@extends('console.layout')

@section('title', 'Novo curso')

@section('topbar.description', 'Cadastro administrativo do curso base, mantendo o mesmo shell do console e sem sair do dashboard.')

@section('topbar.nav')
    <a href="{{ route('admin.cursos.index') }}" class="ui-console-topbar-tab">Cursos</a>
    <span class="ui-console-topbar-tab is-active">Novo curso</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Novo curso"
        subtitle="Crie o curso base com nome, capa e estado editorial antes de cadastrar módulos e aulas."
    >
        <x-slot:actions>
            <a href="{{ route('admin.cursos.index') }}" class="ui-btn-secondary">Voltar</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <form method="POST" action="{{ route('admin.cursos.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
        @csrf

        @include('admin.cursos._form', ['curso' => $curso, 'statuses' => $statuses, 'publicosAlvo' => $publicosAlvo, 'mode' => 'create'])

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.cursos.index') }}" class="ui-btn-secondary">Cancelar</a>
            <button type="submit" class="ui-btn-primary">Salvar</button>
        </div>
    </form>
</div>
@endsection

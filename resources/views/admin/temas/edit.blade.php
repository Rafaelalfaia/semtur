@extends('console.layout')

@section('title', 'Editar tema')

@section('topbar.description', 'Refine tokens, assets, escopos e vigencia do tema sem quebrar o fluxo administrativo ja existente.')

@section('topbar.nav')
    <a href="{{ route('admin.temas.index') }}" class="ui-console-topbar-tab">Temas</a>
    <span class="ui-console-topbar-tab is-active">Editar tema</span>
@endsection

@section('content')
@php
    $isActive = isset($activeTheme) && $activeTheme?->is($theme);
    $isPreview = isset($previewTheme) && $previewTheme?->is($theme);
@endphp

<div class="ui-console-page">
    <x-dashboard.page-header
        title="Editar tema"
        subtitle="Ajuste a experiencia administrativa, aplique preview local e publique quando a identidade estiver pronta."
    >
        <x-slot:actions>
            <a href="{{ route('admin.temas.index') }}" class="ui-btn-secondary">Voltar</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="mt-5 flex flex-wrap items-center gap-2">
        @if($isActive)
            <span class="ui-badge ui-badge-success">Tema ativo</span>
        @endif
        @if($theme->is_default)
            <span class="ui-badge ui-badge-neutral">Tema default</span>
        @endif
        @if($isPreview)
            <span class="ui-badge ui-badge-warning">Preview local ativo</span>
        @endif
        <span class="ui-badge ui-badge-neutral">{{ ucfirst($theme->normalizedStatus()) }}</span>
    </div>

    @if(session('theme_update_visibility_hint'))
        <div class="ui-alert ui-alert-warning mt-4">
            {{ session('theme_update_visibility_hint') }}
        </div>
    @endif

    <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            @can('themes.preview')
                @if($isPreview)
                    <form method="POST" action="{{ route('admin.temas.preview.clear') }}">
                        @csrf
                        <button class="ui-btn-secondary">Limpar preview</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.temas.preview', $theme) }}">
                        @csrf
                        <button class="ui-btn-secondary">Pre-visualizar</button>
                    </form>
                @endif
            @endcan

            @can('themes.activate')
                @if($isActive && ! $theme->is_default)
                    <form method="POST" action="{{ route('admin.temas.restore-default') }}">
                        @csrf
                        @method('PATCH')
                        <button class="ui-btn-secondary">Voltar ao padrao</button>
                    </form>
                @elseif(! $isActive)
                    <form method="POST" action="{{ route('admin.temas.activate', $theme) }}">
                        @csrf
                        @method('PATCH')
                        <button class="ui-btn-secondary">Ativar tema</button>
                    </form>
                @endif
            @endcan

            @can('themes.archive')
                @if($theme->normalizedStatus() !== \App\Models\Theme::STATUS_ARQUIVADO)
                    <form method="POST" action="{{ route('admin.temas.archive', $theme) }}" onsubmit="return confirm('Arquivar este tema?');">
                        @csrf
                        @method('PATCH')
                        <button class="ui-btn-danger">Arquivar</button>
                    </form>
                @endif
            @endcan
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.temas.index') }}" class="ui-btn-secondary">Cancelar</a>
            <button form="theme-update-form" class="ui-btn-primary">Salvar alteracoes</button>
        </div>
    </div>

    <form
        id="theme-update-form"
        method="POST"
        action="{{ route('admin.temas.update', $theme) }}"
        enctype="multipart/form-data"
        class="mt-5 space-y-5"
    >
        @csrf
        @method('PUT')
        @include('admin.temas._form', ['mode' => 'edit'])
    </form>
</div>
@endsection

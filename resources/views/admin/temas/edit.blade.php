@extends('console.layout')

@section('title', 'Editar tema')

@section('topbar.description', 'Refine tokens, assets, escopos e vigência do tema sem quebrar o fluxo administrativo já existente.')

@section('topbar.nav')
    <a href="{{ route('admin.temas.index') }}" class="ui-console-topbar-tab">Temas</a>
    <span class="ui-console-topbar-tab is-active">Editar tema</span>
@endsection

@section('content')
@php
    $isActive = isset($activeTheme) && $activeTheme?->is($theme);
    $isConsoleActive = isset($activeConsoleTheme) && $activeConsoleTheme?->is($theme);
    $isSiteActive = isset($activeSiteTheme) && $activeSiteTheme?->is($theme);
    $isAuthActive = isset($activeAuthTheme) && $activeAuthTheme?->is($theme);
    $isPreview = isset($previewTheme) && $previewTheme?->is($theme);
    $supportsConsole = $theme->appliesTo(\App\Models\Theme::SCOPE_CONSOLE);
    $supportsSite = $theme->appliesTo(\App\Models\Theme::SCOPE_SITE);
    $supportsAuth = $theme->appliesTo(\App\Models\Theme::SCOPE_AUTH);
@endphp

<div class="ui-console-page">
    <x-dashboard.page-header
        title="Editar tema"
        subtitle="Ajuste a experiência administrativa, aplique preview local e publique quando a identidade estiver pronta."
    >
        <x-slot:actions>
            <a href="{{ route('admin.temas.index') }}" class="ui-btn-secondary">Voltar</a>
            @can('themes.view')
                <a href="{{ route('admin.temas.export', $theme) }}" class="ui-btn-secondary">Exportar</a>
            @endcan
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="mt-5 flex flex-wrap items-center gap-2">
        @if($isConsoleActive)
            <span class="ui-badge ui-badge-success">Ativo no console</span>
        @endif
        @if($isSiteActive)
            <span class="ui-badge ui-badge-neutral">Ativo no site</span>
        @endif
        @if($isAuthActive)
            <span class="ui-badge ui-badge-neutral">Ativo no auth</span>
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
                @if($supportsConsole && $isConsoleActive && ! $theme->is_default)
                    <form method="POST" action="{{ route('admin.temas.restore-default') }}">
                        @csrf
                        @method('PATCH')
                        <button class="ui-btn-secondary">Padrão no console</button>
                    </form>
                @elseif($supportsConsole && ! $isConsoleActive)
                    <form method="POST" action="{{ route('admin.temas.activate', $theme) }}">
                        @csrf
                        @method('PATCH')
                        <button class="ui-btn-secondary">Ativar no console</button>
                    </form>
                @endif

                @if($supportsSite && $isSiteActive && ! $theme->is_default)
                    <form method="POST" action="{{ route('admin.temas.restore-default-site') }}">
                        @csrf
                        @method('PATCH')
                        <button class="ui-btn-secondary">Padrão no site</button>
                    </form>
                @elseif($supportsSite && ! $isSiteActive)
                    <form method="POST" action="{{ route('admin.temas.activate-site', $theme) }}">
                        @csrf
                        @method('PATCH')
                        <button class="ui-btn-secondary">Ativar no site</button>
                    </form>
                @endif

                @if($supportsAuth && $isAuthActive && ! $theme->is_default)
                    <form method="POST" action="{{ route('admin.temas.restore-default-auth') }}">
                        @csrf
                        @method('PATCH')
                        <button class="ui-btn-secondary">Padrão no auth</button>
                    </form>
                @elseif($supportsAuth && ! $isAuthActive)
                    <form method="POST" action="{{ route('admin.temas.activate-auth', $theme) }}">
                        @csrf
                        @method('PATCH')
                        <button class="ui-btn-secondary">Ativar no auth</button>
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

            @can('themes.archive')
                @if(! $theme->is_default)
                    <form method="POST" action="{{ route('admin.temas.destroy', $theme) }}" onsubmit="return confirm('Apagar este tema definitivamente? Esta ação remove o tema e seus assets importados.');">
                        @csrf
                        @method('DELETE')
                        <button class="ui-btn-danger">Apagar</button>
                    </form>
                @endif
            @endcan
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.temas.index') }}" class="ui-btn-secondary">Cancelar</a>
            <button form="theme-update-form" class="ui-btn-primary">Salvar alterações</button>
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

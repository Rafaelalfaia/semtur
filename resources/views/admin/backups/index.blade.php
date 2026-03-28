@extends('console.layout')

@section('title', 'Sistema')

@section('topbar.description', 'Módulo operacional do Admin para saúde do sistema, pacotes de backup, integração com R2 e configuração segura.')

@section('topbar.nav')
    <span class="ui-console-topbar-tab is-active">Sistema</span>
    <a href="{{ route('admin.backups.index', ['section' => 'health']) }}" class="ui-console-topbar-tab {{ $activeSection === 'health' ? 'is-active' : '' }}">Saúde</a>
    <a href="{{ route('admin.backups.index', ['section' => 'backups']) }}" class="ui-console-topbar-tab {{ $activeSection === 'backups' ? 'is-active' : '' }}">Backups</a>
    <a href="{{ route('admin.backups.index', ['section' => 'remote']) }}" class="ui-console-topbar-tab {{ $activeSection === 'remote' ? 'is-active' : '' }}">R2</a>
    <a href="{{ route('admin.backups.index', ['section' => 'config']) }}" class="ui-console-topbar-tab {{ $activeSection === 'config' ? 'is-active' : '' }}">Configuração</a>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Sistema"
        subtitle="Área operacional unificada para saúde do ambiente, biblioteca de backups, integração com R2 e manutenção segura."
    >
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.backups.index', ['section' => 'health']) }}" class="{{ $activeSection === 'health' ? 'ui-btn-primary' : 'ui-btn-secondary' }}">Saúde</a>
                <a href="{{ route('admin.backups.index', ['section' => 'backups']) }}" class="{{ $activeSection === 'backups' ? 'ui-btn-primary' : 'ui-btn-secondary' }}">Backups</a>
                <a href="{{ route('admin.backups.index', ['section' => 'remote']) }}" class="{{ $activeSection === 'remote' ? 'ui-btn-primary' : 'ui-btn-secondary' }}">R2</a>
                <a href="{{ route('admin.backups.index', ['section' => 'config']) }}" class="{{ $activeSection === 'config' ? 'ui-btn-primary' : 'ui-btn-secondary' }}">Configuração</a>
            </div>
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="mt-4 rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3 md:p-4">
        <div class="flex flex-wrap gap-2">
            @php
                $sections = [
                    'health' => ['label' => 'Saúde do sistema', 'helper' => 'Peso, áreas, cache e auditoria de mídia.'],
                    'backups' => ['label' => 'Biblioteca local', 'helper' => 'Geração, histórico, importação e download.'],
                    'remote' => ['label' => 'R2 remoto', 'helper' => 'Status remoto, testes e biblioteca no bucket.'],
                    'config' => ['label' => 'Configuração', 'helper' => 'Bucket, endpoint, agenda e retenção.'],
                ];
            @endphp
            @foreach($sections as $key => $section)
                <a href="{{ route('admin.backups.index', ['section' => $key]) }}"
                   class="rounded-[18px] border px-4 py-3 text-left transition {{ $activeSection === $key ? 'border-[var(--ui-primary)] bg-[color-mix(in_srgb,var(--ui-primary)_12%,var(--ui-surface))] text-[var(--ui-text-title)]' : 'border-[var(--ui-border)] bg-[var(--ui-surface)] text-[var(--ui-text-soft)]' }}">
                    <div class="text-sm font-semibold">{{ $section['label'] }}</div>
                    <div class="mt-1 text-xs opacity-80">{{ $section['helper'] }}</div>
                </a>
            @endforeach
        </div>
    </div>

    <div class="mt-5">
        @if($activeSection === 'health')
            @include('admin.backups._section_health')
        @elseif($activeSection === 'backups')
            @include('admin.backups._section_backups')
        @elseif($activeSection === 'remote')
            @include('admin.backups._section_remote')
        @else
            @include('admin.backups._section_config')
        @endif
    </div>
</div>
@endsection

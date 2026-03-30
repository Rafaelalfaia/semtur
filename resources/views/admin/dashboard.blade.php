@extends('console.layout')

@section('title', 'Painel do Admin')

@section('topbar.description', 'Visão executiva do console com foco em leitura rápida, publicações, atividade recente e ações essenciais.')

@section('topbar.nav')
    <span class="ui-console-topbar-tab is-active">Visão executiva</span>
    <a href="#admin-atividade" class="ui-console-topbar-tab">Atividade</a>
    <a href="#admin-atalhos" class="ui-console-topbar-tab">Atalhos</a>
    @if(Route::has('admin.usuarios.index'))
        <a href="{{ route('admin.usuarios.index') }}" class="ui-console-topbar-tab">Usuários</a>
    @endif
@endsection

@section('content')
<div class="ui-console-page ui-admin-dashboard ui-admin-dashboard--compact">
    <x-dashboard.page-header
        title="Visão executiva do console"
        subtitle="Um panorama mais limpo, sofisticado e estável do painel administrativo, com foco apenas no que é principal."
    >
        <x-slot:actions>
            @if(Route::has('admin.usuarios.index'))
                <a href="{{ route('admin.usuarios.index') }}" class="ui-btn-secondary">
                    Usuários
                </a>
            @endif

            @if(Route::has('admin.backups.index'))
                <a href="{{ route('admin.backups.index') }}" class="ui-btn-secondary">
                    Sistema
                </a>
            @endif

            @if(Route::has('admin.temas.index'))
                <a href="{{ route('admin.temas.index') }}" class="ui-btn-primary">
                    {{ $resumo['tema'] }}
                </a>
            @else
                <span class="ui-btn-primary cursor-default opacity-95">
                    {{ $resumo['tema'] }}
                </span>
            @endif
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="ui-admin-dashboard-shell mt-5">
        <x-dashboard.hero-card
            eyebrow="Admin"
            title="Visão executiva do console"
            description="Uma leitura mais enxuta do ambiente administrativo, com destaque para publicações, tração recente e acesso rápido aos fluxos principais."
            class="ui-admin-dashboard-hero ui-admin-dashboard-hero--executive"
        >
            <div class="ui-admin-dashboard-executive-grid">
                <div class="ui-admin-dashboard-executive-main">
                    <div class="ui-admin-dashboard-total-wrap">
                        <div class="ui-dashboard-total-label">Conteúdo publicado</div>
                        <div class="ui-dashboard-total">{{ $resumo['publicados'] }}</div>
                        <p class="ui-dashboard-total-note">
                            {{ $resumo['conteudos'] }} itens monitorados no console com taxa atual de publicação em {{ $resumo['taxa_publicacao'] }}.
                        </p>
                    </div>

                    <div class="ui-admin-dashboard-hero-note">
                        <div class="ui-admin-dashboard-hero-note-kicker">Leitura do momento</div>
                        <p class="ui-admin-dashboard-hero-note-copy">
                            O console já opera com tema global administrado pelo Admin, preservando um shell único, superfícies consistentes e preview seguro antes da ativação.
                        </p>
                    </div>
                </div>

                <div class="ui-admin-dashboard-hero-aside">
                    <div class="ui-admin-dashboard-hero-pill">
                        <span class="ui-admin-dashboard-hero-pill-label">Rascunhos</span>
                        <span class="ui-admin-dashboard-hero-pill-value">{{ $resumo['rascunhos'] }}</span>
                    </div>

                    <div class="ui-admin-dashboard-hero-pill">
                        <span class="ui-admin-dashboard-hero-pill-label">Usuários</span>
                        <span class="ui-admin-dashboard-hero-pill-value">{{ $resumo['usuarios'] }}</span>
                    </div>

                    <div class="ui-admin-dashboard-hero-pill">
                        <span class="ui-admin-dashboard-hero-pill-label">Tema atual</span>
                        <span class="ui-admin-dashboard-hero-pill-value ui-admin-dashboard-hero-pill-value--text">{{ $resumo['tema'] }}</span>
                    </div>
                </div>
            </div>
        </x-dashboard.hero-card>

        <section class="ui-card ui-admin-dashboard-insight-card ui-admin-dashboard-insight-card--compact">
            <div class="ui-admin-dashboard-insight-top">
                <div>
                    <div class="ui-admin-dashboard-insight-kicker">Status do sistema</div>
                    <div class="ui-admin-dashboard-insight-value">{{ $resumo['taxa_publicacao'] }}</div>
                </div>

                <div class="ui-admin-dashboard-gauge" aria-hidden="true">
                    <div class="ui-admin-dashboard-gauge-ring"></div>
                    <div class="ui-admin-dashboard-gauge-core"></div>
                </div>
            </div>

            <p class="ui-admin-dashboard-insight-copy">
                <strong>{{ $resumo['publicados'] }}</strong> publicados, <strong>{{ $resumo['rascunhos'] }}</strong> em preparação e tema <strong>{{ $resumo['tema'] }}</strong> ativo no shell.
            </p>

            <div class="ui-admin-dashboard-chip-list">
                <span class="ui-badge ui-badge-primary">Marca em verde escuro</span>
                <span class="ui-badge ui-badge-neutral">Claro + escuro</span>
            </div>
        </section>
    </div>

    <div class="ui-admin-dashboard-kpi-grid ui-admin-dashboard-kpi-grid--executive mt-4">
        @foreach($metricas as $card)
            <x-dashboard.metric-card
                class="ui-admin-dashboard-kpi-card"
                :label="$card['label']"
                :value="$card['value']"
                :helper="$card['helper']"
                :badge="$card['badge']"
                :badge-tone="$card['tone']"
            />
        @endforeach
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.3fr_0.7fr]">
        <x-dashboard.section-card id="admin-atividade" title="Atividade recente" subtitle="Últimos registros publicados ou atualizados nos módulos principais.">
            @if(! empty($recentes))
                <div class="space-y-3">
                    @foreach($recentes as $item)
                        <div class="flex items-start justify-between gap-4 rounded-2xl border border-[var(--ui-border-soft)] bg-[var(--ui-surface-subtle)] px-4 py-3">
                            <div>
                                <div class="text-sm font-semibold text-[var(--ui-text-title)]">{{ $item['title'] ?? $item['titulo'] ?? 'Sem título' }}</div>
                                <div class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $item['type'] ?? $item['tipo'] ?? 'Registro' }}</div>
                            </div>
                            <div class="text-right text-xs text-[var(--ui-text-soft)]">
                                {{ \Illuminate\Support\Carbon::parse($item['created_at'])->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-[var(--ui-text-soft)]">Nenhuma atividade recente disponível.</p>
            @endif
        </x-dashboard.section-card>

        <div class="grid gap-5">
            <x-dashboard.section-card title="Hierarquia atual" subtitle="Leitura rápida da composição operacional do console.">
                <div class="space-y-3">
                    @foreach($hierarquia as $item)
                        <div class="rounded-2xl border border-[var(--ui-border-soft)] bg-[var(--ui-surface-subtle)] px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-semibold text-[var(--ui-text-title)]">{{ $item['label'] }}</div>
                                <span class="{{ $item['badge_class'] }}">{{ $item['value'] }}</span>
                            </div>
                            <p class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $item['helper'] }}</p>
                        </div>
                    @endforeach
                </div>
            </x-dashboard.section-card>

            <x-dashboard.section-card id="admin-atalhos" title="Atalhos" subtitle="Acesso direto aos fluxos administrativos mais usados.">
                <div class="grid gap-3">
                    @foreach($atalhos as $atalho)
                        @if(! empty($atalho['route']))
                            <a href="{{ $atalho['route'] }}" class="flex items-center justify-between rounded-2xl border border-[var(--ui-border-soft)] bg-[var(--ui-surface-subtle)] px-4 py-3 text-sm font-medium text-[var(--ui-text-title)] transition hover:border-[var(--ui-border-strong)] hover:bg-[var(--ui-surface)]">
                                <span>{{ $atalho['label'] }}</span>
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </x-dashboard.section-card>
        </div>
    </div>

    <x-dashboard.section-card class="mt-5" title="Módulos monitorados" subtitle="Volume por módulo editorial já reconhecido pelo painel.">
        <div class="space-y-4">
            @foreach($modulos as $modulo)
                <div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <div class="font-medium text-[var(--ui-text-title)]">{{ $modulo['nome'] }}</div>
                        <div class="text-[var(--ui-text-soft)]">{{ number_format($modulo['total']) }} · {{ $modulo['status'] }}</div>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-[var(--ui-border-soft)]">
                        <div class="h-full rounded-full bg-[var(--ui-primary)]" style="width: {{ $modulo['percent'] }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-dashboard.section-card>

</div>
@endsection

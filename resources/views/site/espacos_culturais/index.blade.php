@extends('site.layouts.app')

@section('title', 'Museus e teatros em Altamira')
@section('meta.description', 'Agende visitas a museus e ao teatro municipal, consulte horários e veja informações públicas dos espaços culturais de Altamira.')

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;

    $fallback = asset('imagens/altamira.jpg');

    $cards = $espacos->getCollection()->map(function ($espaco) use ($fallback) {
        $capa = $espaco->capa_url ?: optional($espaco->midias->first())->url ?: $fallback;

        return [
            'model' => $espaco,
            'image' => $capa,
            'summary' => $espaco->resumo
                ? $espaco->resumo
                : \Illuminate\Support\Str::limit(strip_tags((string) $espaco->descricao), 140),
            'horarios' => $espaco->horarios->take(3),
        ];
    });
@endphp

<div class="site-page site-page-shell site-espacos-page">
    @include('site.partials._page_hero', [
        'backHref' => localized_route('site.home'),
        'breadcrumbs' => [
            ['label' => 'Início', 'href' => localized_route('site.home')],
            ['label' => 'Museus e teatros'],
        ],
        'badge' => 'Agendamento cultural',
        'title' => 'Museus e teatro municipal',
        'subtitle' => 'Consulte espaços culturais publicados, veja horários e solicite visitas para grupos, escolas e roteiros guiados.',
        'meta' => [
            $espacos->total().' espaços publicados',
            $tipo !== 'todos' ? ucfirst($tipo) : 'Museus e teatro',
            filled($q) ? 'Busca ativa' : null,
        ],
        'primaryActionLabel' => $cards->first() && $cards->first()['model']->agendamento_disponivel ? 'Solicitar visita' : null,
        'primaryActionHref' => $cards->first() && $cards->first()['model']->agendamento_disponivel ? localized_route('site.museus.agendar', ['espaco' => $cards->first()['model']->slug]) : null,
        'secondaryActionLabel' => 'Ver espaços',
        'secondaryActionHref' => '#espacos-lista',
        'image' => $cards->first()['image'] ?? $fallback,
        'imageAlt' => 'Museus e teatros de Altamira',
        'compact' => true,
    ])

    <section class="site-section">
        <div class="site-surface site-espacos-filter-shell">
            <x-section-head
                eyebrow="Agendamento"
                title="Encontre o espaço certo"
                subtitle="Filtre por tipo ou nome para chegar mais rápido ao museu ou ao teatro que faz sentido para sua visita."
            />

            <form method="GET" class="site-search-form site-espacos-search-form">
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Buscar museu, teatro ou bairro"
                    class="w-full rounded-[var(--ui-radius-control)] border border-[var(--ui-border)] bg-[var(--ui-surface-raised)] px-4 py-3 text-sm text-[var(--ui-text)] outline-none focus:border-[var(--ui-primary)] focus:ring-4 focus:ring-[var(--ui-border-focus)]"
                >

                <select
                    name="tipo"
                    class="w-full rounded-[var(--ui-radius-control)] border border-[var(--ui-border)] bg-[var(--ui-surface-raised)] px-4 py-3 text-sm text-[var(--ui-text)] outline-none focus:border-[var(--ui-primary)] focus:ring-4 focus:ring-[var(--ui-border-focus)]"
                >
                    <option value="todos" @selected($tipo === 'todos')>Museus e teatro</option>
                    <option value="museu" @selected($tipo === 'museu')>Museus</option>
                    <option value="teatro" @selected($tipo === 'teatro')>Teatro</option>
                </select>

                <button type="submit" class="site-button-primary">Aplicar</button>
            </form>
        </div>
    </section>

    <section class="site-section" id="espacos-lista">
        <x-section-head
            eyebrow="Espaços"
            title="Agende sua visita"
            subtitle="Cada página traz informações públicas do espaço, grade semanal e o formulário de solicitação quando o agendamento estiver disponível."
        />

        @if($cards->isEmpty())
            <div class="site-empty-state">
                <p class="site-empty-state-title">Nenhum espaço encontrado</p>
                <p class="site-empty-state-copy">Tente ajustar a busca ou trocar o tipo para ver outros espaços culturais publicados.</p>
            </div>
        @else
            <div class="site-espacos-grid">
                @foreach($cards as $item)
                    @php($espaco = $item['model'])

                    <article class="site-surface site-espacos-card">
                        <a href="{{ localized_route('site.museus.show', ['slug' => $espaco->slug]) }}" class="site-espacos-card-media">
                            <img src="{{ site_image_url($item['image'], 'card') }}" alt="{{ $espaco->nome }}" class="site-espacos-card-image" loading="lazy" decoding="async">
                        </a>

                        <div class="site-espacos-card-body">
                            <div class="site-espacos-card-head">
                                <div class="site-espacos-card-copy">
                                    <div class="site-espacos-card-badges">
                                        <span class="site-badge">{{ $espaco->tipo_label }}</span>
                                        @if($espaco->agendamento_disponivel)
                                            <span class="site-filter-chip is-active">Agendamento disponível</span>
                                        @endif
                                    </div>

                                    <h3 class="site-espacos-card-title">
                                        <a href="{{ localized_route('site.museus.show', ['slug' => $espaco->slug]) }}">{{ $espaco->nome }}</a>
                                    </h3>

                                    <div class="site-card-list-meta">
                                        @if($espaco->bairro)
                                            <span>{{ $espaco->bairro }}</span>
                                        @endif
                                        <span>{{ $espaco->cidade ?: 'Altamira' }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($item['summary'])
                                <p class="site-card-list-summary">{{ $item['summary'] }}</p>
                            @endif

                            @if($item['horarios']->isNotEmpty())
                                <div class="site-espacos-schedule-list">
                                    @foreach($item['horarios'] as $horario)
                                        <div class="site-espacos-schedule-item">
                                            <span class="site-espacos-schedule-day">{{ $horario->dia_label }}</span>
                                            <span class="site-espacos-schedule-time">{{ $horario->faixa_label }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="site-inline-actions site-espacos-card-actions">
                                <a href="{{ localized_route('site.museus.show', ['slug' => $espaco->slug]) }}" class="site-button-secondary">Ver espaço</a>
                                @if($espaco->agendamento_disponivel)
                                    <a href="{{ localized_route('site.museus.agendar', ['espaco' => $espaco->slug]) }}" class="site-button-primary">Agendar visita</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($espacos->hasPages())
                <div class="site-surface-soft site-espacos-pagination-shell">
                    {{ $espacos->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection


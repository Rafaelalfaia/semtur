@extends('site.layouts.app')

@section('title', ui_text('ui.spaces.title'))
@section('meta.description', ui_text('ui.spaces.meta_description'))

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
            ['label' => ui_text('ui.common.home'), 'href' => localized_route('site.home')],
            ['label' => ui_text('ui.spaces.breadcrumb')],
        ],
        'badge' => ui_text('ui.spaces.badge'),
        'title' => ui_text('ui.spaces.hero_title'),
        'subtitle' => ui_text('ui.spaces.hero_subtitle'),
        'meta' => [
            $espacos->total().' '.ui_text('ui.spaces.published_spaces'),
            $tipo !== 'todos' ? ucfirst($tipo) : ui_text('ui.spaces.all_types'),
            filled($q) ? ui_text('ui.spaces.active_search') : null,
        ],
        'primaryActionLabel' => $cards->first() && $cards->first()['model']->agendamento_disponivel ? ui_text('ui.spaces.request_visit') : null,
        'primaryActionHref' => $cards->first() && $cards->first()['model']->agendamento_disponivel ? localized_route('site.museus.agendar', ['espaco' => $cards->first()['model']->slug]) : null,
        'secondaryActionLabel' => ui_text('ui.spaces.view_spaces'),
        'secondaryActionHref' => '#espacos-lista',
        'image' => $cards->first()['image'] ?? $fallback,
        'imageAlt' => ui_text('ui.spaces.image_alt'),
        'compact' => true,
    ])

    <section class="site-section">
        <div class="site-surface site-espacos-filter-shell">
            <x-section-head
                :eyebrow="ui_text('ui.spaces.filter_eyebrow')"
                :title="ui_text('ui.spaces.filter_title')"
                :subtitle="ui_text('ui.spaces.filter_subtitle')"
            />

            <form method="GET" class="site-search-form site-espacos-search-form">
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="{{ ui_text('ui.spaces.search_placeholder') }}"
                    class="w-full rounded-[var(--ui-radius-control)] border border-[var(--ui-border)] bg-[var(--ui-surface-raised)] px-4 py-3 text-sm text-[var(--ui-text)] outline-none focus:border-[var(--ui-primary)] focus:ring-4 focus:ring-[var(--ui-border-focus)]"
                >

                <select
                    name="tipo"
                    class="w-full rounded-[var(--ui-radius-control)] border border-[var(--ui-border)] bg-[var(--ui-surface-raised)] px-4 py-3 text-sm text-[var(--ui-text)] outline-none focus:border-[var(--ui-primary)] focus:ring-4 focus:ring-[var(--ui-border-focus)]"
                >
                    <option value="todos" @selected($tipo === 'todos')>{{ ui_text('ui.spaces.all_types') }}</option>
                    <option value="museu" @selected($tipo === 'museu')>{{ ui_text('ui.spaces.museums') }}</option>
                    <option value="teatro" @selected($tipo === 'teatro')>{{ ui_text('ui.spaces.theater') }}</option>
                </select>

                <button type="submit" class="site-button-primary">{{ ui_text('ui.common.apply') }}</button>
            </form>
        </div>
    </section>

    <section class="site-section" id="espacos-lista">
        <x-section-head
            :eyebrow="ui_text('ui.spaces.section_eyebrow')"
            :title="ui_text('ui.spaces.section_title')"
            :subtitle="ui_text('ui.spaces.section_subtitle')"
        />

        @if($cards->isEmpty())
            <div class="site-empty-state">
                <p class="site-empty-state-title">{{ ui_text('ui.spaces.empty_title') }}</p>
                <p class="site-empty-state-copy">{{ ui_text('ui.spaces.hero_subtitle') }}</p>
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
                                            <span class="site-filter-chip is-active">{{ ui_text('ui.spaces.available_badge') }}</span>
                                        @endif
                                    </div>

                                    <h3 class="site-espacos-card-title">
                                        <a href="{{ localized_route('site.museus.show', ['slug' => $espaco->slug]) }}">{{ $espaco->nome }}</a>
                                    </h3>

                                    <div class="site-card-list-meta">
                                        @if($espaco->bairro)
                                            <span>{{ $espaco->bairro }}</span>
                                        @endif
                                        <span>{{ $espaco->cidade ?: ui_text('ui.common.altamira') }}</span>
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
                                <a href="{{ localized_route('site.museus.show', ['slug' => $espaco->slug]) }}" class="site-button-secondary">{{ ui_text('ui.spaces.view_space') }}</a>
                                @if($espaco->agendamento_disponivel)
                                    <a href="{{ localized_route('site.museus.agendar', ['espaco' => $espaco->slug]) }}" class="site-button-primary">{{ ui_text('ui.spaces.schedule_visit') }}</a>
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

@extends('site.layouts.app')

@section('title', $material->nome . ' • ' . $material->tipo_label . ' • VisitAltamira')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) $material->descricao), 160))
@section('meta.image', $material->capa_url ?: asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $cover = $material->capa_url ?: asset('imagens/altamira.jpg');
    $embedUrl = $material->embed_url;
    $guiasUrl = Route::has('site.guias') ? localized_route('site.guias') : '#';
    $homeUrl = localized_route('site.home');
    $relatedItems = collect($relacionados ?? []);
@endphp

<div class="site-page site-page-shell">
    @include('site.partials._page_hero', [
        'backHref' => $guiasUrl,
        'breadcrumbs' => [
            ['label' => ui_text('ui.common.home'), 'href' => $homeUrl],
            ['label' => ui_text('ui.guides.index_title'), 'href' => $guiasUrl],
            ['label' => $material->nome],
        ],
        'badge' => $material->tipo_label,
        'title' => $material->nome,
        'subtitle' => Str::limit(strip_tags((string) $material->descricao), 180),
        'meta' => [],
        'primaryActionLabel' => ui_text('ui.guides.read_material'),
        'primaryActionHref' => '#leitura',
        'secondaryActionLabel' => ui_text('ui.common.back_to_guides'),
        'secondaryActionHref' => $guiasUrl,
        'image' => $cover,
        'imageAlt' => $material->nome,
        'compact' => true,
    ])

    <section class="site-section">
        <div class="site-editorial-layout">
            <div class="site-editorial-main">
                <section class="site-surface site-content-block">
                    <x-section-head
                        :eyebrow="ui_text('ui.guides.about_material')"
                        :title="ui_text('ui.guides.material_information', ['type' => Str::lower($material->tipo_label)])"
                        :subtitle="ui_text('ui.guides.editorial_summary')"
                    />

                    <div class="site-prose">
                        {!! nl2br(e($material->descricao)) !!}
                    </div>
                </section>

                <section id="leitura" class="site-surface site-content-block">
                    <x-section-head
                        :eyebrow="ui_text('ui.guides.reading')"
                        :title="ui_text('ui.guides.material_viewing')"
                        :subtitle="ui_text('ui.guides.embedded_copy')"
                    />

                    <div class="site-inline-actions">
                        @if(filled($material->link_acesso))
                            <a
                                href="{{ $material->link_acesso }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="site-button-secondary"
                            >
                                {{ ui_text('ui.guides.open_drive') }}
                            </a>
                        @endif
                    </div>

                    @if($embedUrl)
                        <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white">
                            <iframe
                                src="{{ $embedUrl }}"
                                title="{{ $material->nome }}"
                                class="h-[75vh] min-h-[620px] w-full"
                                loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen
                            ></iframe>
                        </div>

                        <p class="site-empty-state-copy">
                            {{ ui_text('ui.guides.viewer_fallback') }}
                        </p>
                    @else
                        <div class="site-empty-state">
                            <p class="site-empty-state-title">{{ ui_text('ui.guides.preview_unavailable') }}</p>
                            <p class="site-empty-state-copy">{{ ui_text('ui.guides.preview_unavailable_copy') }}</p>

                            @if(filled($material->link_acesso))
                                <a
                                    href="{{ $material->link_acesso }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="site-button-primary"
                                >
                                    {{ ui_text('ui.guides.open_material') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </section>
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    <x-section-head :eyebrow="ui_text('ui.common.summary')" :title="ui_text('ui.guides.overview')" />

                    <div class="site-stats-grid">
                        <div class="site-stat-card">
                            <span class="site-stat-label">{{ ui_text('ui.guides.type') }}</span>
                            <span class="site-stat-value">{{ $material->tipo_label }}</span>
                        </div>
                    </div>

                    <div class="site-inline-actions">
                        <a href="{{ $guiasUrl }}" class="site-button-secondary">{{ ui_text('ui.guides.view_more_materials') }}</a>
                    </div>
                </section>

                @if($relatedItems->isNotEmpty())
                    <section class="site-surface-soft site-content-block">
                        <x-section-head
                            :eyebrow="ui_text('ui.guides.related')"
                            :title="ui_text('ui.guides.more_materials', ['type' => Str::plural(Str::lower($material->tipo_label), 2)])"
                        />

                        <div class="space-y-4">
                            @foreach($relatedItems as $rel)
                                <a
                                    href="{{ localized_route('site.guias.show', ['slug' => $rel->slug]) }}"
                                    class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-emerald-300 hover:bg-emerald-50"
                                >
                                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">
                                        {{ $rel->tipo_label }}
                                    </div>
                                    <div class="mt-2 text-sm font-semibold text-slate-900">
                                        {{ $rel->nome }}
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        {{ Str::limit(strip_tags((string) $rel->descricao), 90) }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </section>
</div>
@endsection

@extends('site.layouts.app')

@section('title', $video->titulo . ' • ' . ui_text('ui.videos.title') . ' • VisitAltamira')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) $video->descricao), 160))
@section('meta.image', $video->capa_url ?: asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Str;

    $cover = $heroMedia?->url ?: ($video->capa_url ?: asset('imagens/altamira.jpg'));
    $embedUrl = $video->embed_url;
    $heroBadge = $heroTranslation?->eyebrow ?: ui_text('ui.common.video');
    $heroTitle = $heroTranslation?->titulo ?: $video->titulo;
    $heroSubtitle = $heroTranslation?->lead ?: Str::limit(strip_tags((string) $video->descricao), 240);
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ui_text('ui.home.watch_now');
    $heroPrimaryHref = $heroTranslation?->cta_href ?: '#visualizacao';
@endphp

<section class="relative isolate overflow-hidden bg-[#07131C] text-white">
    <div class="absolute inset-0">
        <img src="{{ $cover }}" alt="{{ $video->titulo }}" class="h-full w-full object-cover opacity-30" loading="eager" decoding="async">
        <div class="absolute inset-0 bg-gradient-to-b from-[#07131C]/55 via-[#07131C]/82 to-[#07131C]"></div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 pb-14 pt-10 sm:px-6 lg:px-8 lg:pb-20 lg:pt-14">
        <div class="max-w-3xl">
            <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.20em] text-cyan-100">{{ $heroBadge }}</span>
            <h1 class="mt-5 text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">{{ $heroTitle }}</h1>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-200 sm:text-base">{{ $heroSubtitle }}</p>

            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ $heroPrimaryHref }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-cyan-500">{{ $heroPrimaryLabel }}</a>
                <a href="{{ localized_route('site.videos') }}" class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-medium text-white transition hover:bg-white/15">{{ ui_text('ui.common.back_to_videos') }}</a>
            </div>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-3 lg:mt-10 lg:max-w-3xl">
            <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                <div class="text-[11px] uppercase tracking-[0.18em] text-slate-300">{{ ui_text('ui.common.type') }}</div>
                <div class="mt-2 text-base font-semibold text-white">{{ ui_text('ui.common.video') }}</div>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                <div class="text-[11px] uppercase tracking-[0.18em] text-slate-300">{{ ui_text('ui.common.published') }}</div>
                <div class="mt-2 text-base font-semibold text-white">{{ optional($video->published_at)->format('d/m/Y') ?: ui_text('ui.common.available') }}</div>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                <div class="text-[11px] uppercase tracking-[0.18em] text-slate-300">{{ ui_text('ui.common.access') }}</div>
                <div class="mt-2 text-base font-semibold text-white">{{ ui_text('ui.videos.portal_view') }}</div>
            </div>
        </div>
    </div>
</section>

<section class="bg-white">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-cyan-700">{{ ui_text('ui.videos.about_video') }}</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ ui_text('ui.videos.content_information') }}</h2>
                    <div class="mt-4 space-y-4 text-[15px] leading-8 text-slate-600">{!! nl2br(e($video->descricao)) !!}</div>
                </section>

                <section id="visualizacao" class="rounded-[28px] border border-slate-200 bg-[#F4FBFD] p-6 shadow-sm sm:p-7">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-cyan-700">{{ ui_text('ui.videos.viewing') }}</div>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ ui_text('ui.videos.video_display') }}</h2>
                            <p class="mt-2 text-sm leading-7 text-slate-500">{{ ui_text('ui.videos.embedded_copy') }}</p>
                        </div>

                        <a href="{{ $video->link_acesso }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">{{ ui_text('ui.videos.open_on_drive') }}</a>
                    </div>

                    @if($embedUrl)
                        <div class="mt-6 overflow-hidden rounded-[24px] border border-slate-200 bg-white">
                            <iframe src="{{ $embedUrl }}" title="{{ $video->titulo }}" class="h-[75vh] min-h-[620px] w-full" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        </div>
                        <p class="mt-3 text-xs leading-6 text-slate-500">{{ ui_text('ui.videos.viewer_fallback') }}</p>
                    @else
                        <div class="mt-6 rounded-[24px] border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                            <h3 class="text-lg font-semibold text-slate-900">{{ ui_text('ui.videos.preview_unavailable') }}</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-500">{{ ui_text('ui.videos.preview_unavailable_copy') }}</p>
                            <div class="mt-4">
                                <a href="{{ $video->link_acesso }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-cyan-500">{{ ui_text('ui.videos.open_video') }}</a>
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-cyan-700">{{ ui_text('ui.common.summary') }}</div>
                    <h2 class="mt-2 text-lg font-semibold text-slate-900">{{ ui_text('ui.common.general_overview') }}</h2>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">{{ ui_text('ui.common.type') }}</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ ui_text('ui.common.video') }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">{{ ui_text('ui.common.published_in') }}</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ optional($video->published_at)->format('d/m/Y') ?: '—' }}</div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <a href="{{ localized_route('site.videos') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">{{ ui_text('ui.videos.view_more_videos') }}</a>
                    </div>
                </section>

                @if(($relacionados ?? collect())->count())
                    <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-cyan-700">{{ ui_text('ui.videos.related') }}</div>
                        <h2 class="mt-2 text-lg font-semibold text-slate-900">{{ ui_text('ui.videos.more_videos') }}</h2>

                        <div class="mt-5 space-y-4">
                            @foreach($relacionados as $item)
                                <a href="{{ localized_route('site.videos.show', ['slug' => $item->slug]) }}" class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-cyan-300 hover:bg-cyan-50">
                                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">{{ ui_text('ui.common.video') }}</div>
                                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $item->titulo }}</div>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ Str::limit(strip_tags((string) $item->descricao), 90) }}</p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</section>

@include('site.partials._content_editor', [
    'editorTitle' => $heroTitle,
    'editorPage' => 'site.videos.show',
    'editorKey' => 'hero',
    'editorLabel' => 'Hero detalhe de vídeo',
    'editorLocale' => route_locale(),
    'editableTranslation' => $heroTranslation ?? null,
    'editableHeroMedia' => $heroMedia ?? null,
    'editableStatus' => $heroBlock?->status ?? 'publicado',
    'editableFallback' => [
        'eyebrow' => $heroBadge,
        'titulo' => $heroTitle,
        'subtitulo' => null,
        'lead' => $heroSubtitle,
        'conteudo' => null,
        'cta_label' => $heroPrimaryLabel,
        'cta_href' => $heroPrimaryHref,
        'seo_title' => $heroTitle,
        'seo_description' => \Illuminate\Support\Str::limit(strip_tags((string) $video->descricao), 160),
    ],
])
@endsection



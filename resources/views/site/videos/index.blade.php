@extends('site.layouts.app')

@section('title', ui_text('ui.videos.title') . ' • VisitAltamira')
@section('meta.description', $heroTranslation?->seo_description ?: ($heroTranslation?->lead ?: ui_text('ui.videos.meta_description')))
@section('meta.image', $heroMedia?->url ?: asset('imagens/altamira.jpg'))
@section('title', ($heroTranslation?->seo_title ?: ($heroTranslation?->titulo ?: ui_text('ui.videos.title'))) . ' â€¢ VisitAltamira')

@section('site.content')
@php
    use Illuminate\Support\Str;

    $qAtual = (string) ($q ?? '');
    $totalVideos = method_exists($videos, 'total') ? $videos->total() : $videos->count();
    $heroBadge = $heroTranslation?->eyebrow ?: ui_text('ui.videos.hero_badge');
    $heroTitle = $heroTranslation?->titulo ?: ui_text('ui.videos.hero_title');
    $heroSubtitle = $heroTranslation?->lead ?: ui_text('ui.videos.hero_subtitle');
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ui_text('ui.videos.explore_videos');
    $heroPrimaryHref = $heroTranslation?->cta_href ?: '#lista-videos';
    $heroImage = $heroMedia?->url;
    $heroStyle = $heroImage
        ? "background-image: linear-gradient(135deg, rgba(3, 105, 161, 0.88), rgba(8, 145, 178, 0.7), rgba(2, 6, 23, 0.92)), url('{$heroImage}'); background-size: cover; background-position: center;"
        : null;
@endphp

<section class="bg-slate-950 text-white">
    <div class="mx-auto max-w-[1200px] px-4 py-6 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[28px] border border-white/10 bg-gradient-to-br from-sky-700 via-cyan-800 to-slate-950" @if($heroStyle) style="{{ $heroStyle }}" @endif>
            <div class="grid gap-8 px-5 py-8 sm:px-8 lg:grid-cols-[1.35fr_.9fr] lg:px-10 lg:py-12">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-100">
                        {{ $heroBadge }}
                    </div>

                    <h1 class="mt-4 text-3xl font-semibold leading-tight sm:text-4xl">
                        {{ $heroTitle }}
                    </h1>

                    <p class="mt-4 max-w-xl text-sm leading-7 text-cyan-50/90 sm:text-base">
                        {{ $heroSubtitle }}
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ $heroPrimaryHref }}" class="inline-flex items-center rounded-2xl bg-white px-5 py-3 text-sm font-medium text-slate-900 transition hover:bg-cyan-50">
                            {{ $heroPrimaryLabel }}
                        </a>

                        <a href="{{ localized_route('site.explorar') }}" class="inline-flex items-center rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-medium text-white transition hover:bg-white/15">
                            {{ ui_text('ui.videos.view_more_options') }}
                        </a>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-cyan-100/80">{{ ui_text('ui.videos.collection') }}</div>
                        <div class="mt-2 text-lg font-semibold">{{ $totalVideos }} {{ ui_text('ui.videos.title') }}</div>
                        <p class="mt-2 text-sm leading-6 text-cyan-50/85">{{ ui_text('ui.videos.collection_copy') }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-cyan-100/80">{{ ui_text('ui.videos.reading') }}</div>
                        <div class="mt-2 text-lg font-semibold">{{ ui_text('ui.videos.portal_view') }}</div>
                        <p class="mt-2 text-sm leading-6 text-cyan-50/85">{{ ui_text('ui.videos.portal_view_copy') }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-cyan-100/80">{{ ui_text('ui.videos.origin') }}</div>
                        <div class="mt-2 text-lg font-semibold">{{ ui_text('ui.common.official_curation') }}</div>
                        <p class="mt-2 text-sm leading-6 text-cyan-50/85">{{ ui_text('ui.videos.official_curation_copy') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="lista-videos" class="bg-slate-950 pb-16 text-white">
    <div class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="h-fit rounded-[26px] border border-white/10 bg-white/[0.03] p-5">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-300/80">{{ ui_text('ui.common.filters') }}</div>
                <h2 class="mt-2 text-lg font-semibold text-slate-100">{{ ui_text('ui.videos.find_video') }}</h2>

                <form method="GET" class="mt-5 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm text-slate-300">{{ ui_text('ui.videos.search_label') }}</label>
                        <input type="text" name="q" value="{{ $qAtual }}" placeholder="{{ ui_text('ui.videos.search_placeholder') }}" class="w-full rounded-2xl border border-white/10 bg-slate-900/80 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <button type="submit" class="inline-flex items-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-cyan-500">{{ ui_text('ui.videos.apply_filters') }}</button>
                        <a href="{{ localized_route('site.videos') }}" class="inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-medium text-slate-200 transition hover:bg-white/10">{{ ui_text('ui.common.clear') }}</a>
                    </div>
                </form>
            </aside>

            <div>
                <div class="mb-5">
                    <h2 class="text-2xl font-semibold text-slate-100">{{ ui_text('ui.videos.published_videos') }}</h2>
                    <p class="mt-1 text-sm text-slate-400">
                        {{ $totalVideos }} {{ $totalVideos === 1 ? ui_text('ui.videos.single_found') : ui_text('ui.videos.multiple_found') }}
                    </p>
                </div>

                @if($totalVideos === 0)
                    <div class="rounded-[28px] border border-dashed border-white/10 bg-white/[0.03] px-6 py-14 text-center">
                        <h3 class="text-xl font-semibold text-slate-100">{{ ui_text('ui.videos.empty_title') }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-400">{{ ui_text('ui.videos.empty_copy') }}</p>
                    </div>
                @else
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($videos as $video)
                            @php $cover = $video->capa_url ?: asset('imagens/altamira.jpg'); @endphp
                            <article class="overflow-hidden rounded-[26px] border border-white/10 bg-white/[0.03]">
                                <div class="relative h-56 overflow-hidden bg-slate-900">
                                    <img src="{{ $cover }}" alt="{{ $video->titulo }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent"></div>
                                    <div class="absolute inset-x-0 bottom-0 p-4">
                                        <span class="inline-flex rounded-full border border-white/15 bg-black/35 px-3 py-1 text-xs text-white">{{ ui_text('ui.common.video') }}</span>
                                    </div>
                                </div>

                                <div class="p-5">
                                    <h3 class="text-lg font-semibold text-slate-100">{{ $video->titulo }}</h3>
                                    <p class="mt-3 text-sm leading-7 text-slate-300">{{ Str::limit(strip_tags((string) $video->descricao), 140) }}</p>
                                    <div class="mt-5 flex flex-wrap gap-2">
                                        <a href="{{ localized_route('site.videos.show', ['slug' => $video->slug]) }}" class="inline-flex items-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-cyan-500">{{ ui_text('ui.videos.open_video') }}</a>
                                        <a href="{{ $video->link_acesso }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-white/10">{{ ui_text('ui.videos.google_drive') }}</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if(method_exists($videos, 'links'))
                        <div class="mt-8">{{ $videos->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</section>

@include('site.partials._content_editor', [
    'editorTitle' => $heroTitle,
    'editorPage' => 'site.videos',
    'editorKey' => 'hero',
    'editorLabel' => 'Hero Vídeos',
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
        'seo_description' => $heroTranslation?->seo_description ?: ui_text('ui.videos.meta_description'),
    ],
])
@endsection

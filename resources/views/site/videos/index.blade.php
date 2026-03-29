@extends('site.layouts.app')

@section('title', __('ui.videos.title') . ' • VisitAltamira')
@section('meta.description', __('ui.videos.meta_description'))
@section('meta.image', asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Str;

    $qAtual = (string) ($q ?? '');
    $totalVideos = method_exists($videos, 'total') ? $videos->total() : $videos->count();
@endphp

<section class="bg-slate-950 text-white">
    <div class="mx-auto max-w-[1200px] px-4 py-6 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[28px] border border-white/10 bg-gradient-to-br from-sky-700 via-cyan-800 to-slate-950">
            <div class="grid gap-8 px-5 py-8 sm:px-8 lg:grid-cols-[1.35fr_.9fr] lg:px-10 lg:py-12">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-100">
                        {{ __('ui.videos.hero_badge') }}
                    </div>

                    <h1 class="mt-4 text-3xl font-semibold leading-tight sm:text-4xl">
                        {{ __('ui.videos.hero_title') }}
                    </h1>

                    <p class="mt-4 max-w-xl text-sm leading-7 text-cyan-50/90 sm:text-base">
                        {{ __('ui.videos.hero_subtitle') }}
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="#lista-videos" class="inline-flex items-center rounded-2xl bg-white px-5 py-3 text-sm font-medium text-slate-900 transition hover:bg-cyan-50">
                            {{ __('ui.videos.explore_videos') }}
                        </a>

                        <a href="{{ localized_route('site.explorar') }}" class="inline-flex items-center rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-medium text-white transition hover:bg-white/15">
                            {{ __('ui.videos.view_more_options') }}
                        </a>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-cyan-100/80">{{ __('ui.videos.collection') }}</div>
                        <div class="mt-2 text-lg font-semibold">{{ $totalVideos }} {{ __('ui.videos.title') }}</div>
                        <p class="mt-2 text-sm leading-6 text-cyan-50/85">{{ __('ui.videos.collection_copy') }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-cyan-100/80">{{ __('ui.videos.reading') }}</div>
                        <div class="mt-2 text-lg font-semibold">{{ __('ui.videos.portal_view') }}</div>
                        <p class="mt-2 text-sm leading-6 text-cyan-50/85">{{ __('ui.videos.portal_view_copy') }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-cyan-100/80">{{ __('ui.videos.origin') }}</div>
                        <div class="mt-2 text-lg font-semibold">{{ __('ui.common.official_curation') }}</div>
                        <p class="mt-2 text-sm leading-6 text-cyan-50/85">{{ __('ui.videos.official_curation_copy') }}</p>
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
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-300/80">{{ __('ui.common.filters') }}</div>
                <h2 class="mt-2 text-lg font-semibold text-slate-100">{{ __('ui.videos.find_video') }}</h2>

                <form method="GET" class="mt-5 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm text-slate-300">{{ __('ui.videos.search_label') }}</label>
                        <input type="text" name="q" value="{{ $qAtual }}" placeholder="{{ __('ui.videos.search_placeholder') }}" class="w-full rounded-2xl border border-white/10 bg-slate-900/80 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <button type="submit" class="inline-flex items-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-cyan-500">{{ __('ui.videos.apply_filters') }}</button>
                        <a href="{{ localized_route('site.videos') }}" class="inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-medium text-slate-200 transition hover:bg-white/10">{{ __('ui.common.clear') }}</a>
                    </div>
                </form>
            </aside>

            <div>
                <div class="mb-5">
                    <h2 class="text-2xl font-semibold text-slate-100">{{ __('ui.videos.published_videos') }}</h2>
                    <p class="mt-1 text-sm text-slate-400">
                        {{ $totalVideos }} {{ $totalVideos === 1 ? __('ui.videos.single_found') : __('ui.videos.multiple_found') }}
                    </p>
                </div>

                @if($totalVideos === 0)
                    <div class="rounded-[28px] border border-dashed border-white/10 bg-white/[0.03] px-6 py-14 text-center">
                        <h3 class="text-xl font-semibold text-slate-100">{{ __('ui.videos.empty_title') }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-400">{{ __('ui.videos.empty_copy') }}</p>
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
                                        <span class="inline-flex rounded-full border border-white/15 bg-black/35 px-3 py-1 text-xs text-white">{{ __('ui.common.video') }}</span>
                                    </div>
                                </div>

                                <div class="p-5">
                                    <h3 class="text-lg font-semibold text-slate-100">{{ $video->titulo }}</h3>
                                    <p class="mt-3 text-sm leading-7 text-slate-300">{{ Str::limit(strip_tags((string) $video->descricao), 140) }}</p>
                                    <div class="mt-5 flex flex-wrap gap-2">
                                        <a href="{{ localized_route('site.videos.show', ['slug' => $video->slug]) }}" class="inline-flex items-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-cyan-500">{{ __('ui.videos.open_video') }}</a>
                                        <a href="{{ $video->link_acesso }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-white/10">{{ __('ui.videos.google_drive') }}</a>
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
@endsection

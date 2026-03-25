@extends('layouts.app')

@push('head')
    <x-seo
        :title="trim($__env->yieldContent('title', 'VisitAltamira'))"
        :description="trim($__env->yieldContent('meta.description', 'Portal turistico de Altamira'))"
        :image="trim($__env->yieldContent('meta.image', theme_asset('hero_image')))"
        :canonical="trim($__env->yieldContent('meta.canonical', url()->current()))"
        :type="trim($__env->yieldContent('meta.type', 'website'))"
        :noindex="in_array(strtolower(trim($__env->yieldContent('meta.noindex', 'false'))), ['1', 'true', 'yes', 'on'], true)"
    >
        @stack('structured-data')
    </x-seo>

    <style>
        @if(! empty($resolvedThemeCssVariables))
        :root {
            @foreach($resolvedThemeCssVariables as $variable => $value)
            {{ $variable }}: {{ $value }};
            @endforeach
        }
        @endif
    </style>
@endpush

@section('content')
    <div class="site-shell" data-site-theme="{{ $resolvedThemeDataTheme ?? 'default' }}">
        @include('site.partials._top_nav')

        <main class="site-main">
            @yield('site.content')
        </main>

        @include('site.partials._footer')
        @include('site.partials._bottom_nav')
    </div>

    <x-aviso-popup :aviso="$aviso ?? null" />

    @stack('scripts')
@endsection

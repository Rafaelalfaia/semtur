@extends('site.layouts.app')

@section('title', ui_text('ui.auth.forgot_password'))
@section('meta.noindex', 'true')

@section('site.content')
@php
    $forgotImage = asset('imagens/altamira.jpg');
    $forgotImageSources = site_image_sources($forgotImage, 'hero');
    $authLogo = asset('imagens/logosemtur.png');
    $logoSources = site_image_sources($authLogo, 'logo');
@endphp
<div class="ui-auth-shell">
    <div class="ui-auth-card">
        <div class="ui-auth-media">
            <x-picture
                :jpg="$forgotImageSources['jpg'] ?? $forgotImage"
                :webp="$forgotImageSources['webp'] ?? null"
                alt="Paisagem de Altamira"
                class="ui-auth-media-image"
                sizes="(max-width: 1024px) 100vw, 50vw"
                :width="$forgotImageSources['width'] ?? null"
                :height="$forgotImageSources['height'] ?? null"
                priority
            />
            <div class="ui-auth-media-overlay"></div>
            <div class="ui-auth-media-copy">
                <x-picture
                    :jpg="$logoSources['jpg'] ?? $authLogo"
                    :webp="$logoSources['webp'] ?? null"
                    alt="{{ ui_text('ui.auth.logo_alt') }}"
                    class="ui-auth-media-logo"
                    sizes="180px"
                    :width="$logoSources['width'] ?? null"
                    :height="$logoSources['height'] ?? null"
                    priority
                />
                <div>
                    <div class="ui-auth-eyebrow">{{ ui_text('ui.auth.eyebrow') }}</div>
                    <h1 class="ui-auth-heading">{{ ui_text('ui.auth.forgot_password') }}</h1>
                    <p class="ui-auth-subtitle">{{ ui_text('ui.auth.forgot_copy') }}</p>
                </div>
            </div>
        </div>

        <div class="ui-auth-form-panel">
            <div>
                <div class="ui-auth-eyebrow">{{ ui_text('ui.auth.reset_link') }}</div>
                <h2 class="ui-auth-title">{{ ui_text('ui.auth.forgot_password') }}</h2>
                <p class="ui-auth-copy">{{ ui_text('ui.auth.forgot_copy') }}</p>
            </div>

            <form method="POST" action="{{ route('password.email') }}" class="ui-auth-form" novalidate>
                @csrf

                @if(session('status'))
                    <div class="ui-alert ui-alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <div>
                    <label for="email" class="ui-form-label">{{ ui_text('ui.auth.email') }}</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                        class="ui-input"
                    >
                    @error('email')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="ui-btn-primary w-full justify-center">
                    {{ ui_text('ui.auth.reset_link') }}
                </button>

                @if(Route::has('login'))
                    <p class="text-center text-sm text-[var(--ui-text-soft)]">
                        Lembrou sua senha?
                        <a href="{{ route('login') }}" class="font-medium text-[var(--ui-primary)] hover:underline">{{ ui_text('ui.auth.login_panel') }}</a>
                    </p>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection

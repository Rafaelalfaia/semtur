@extends('site.layouts.app')

@section('title', ui_text('ui.auth.login_title'))
@section('meta.noindex', 'true')

@section('site.content')
@php
    $loginImage = asset('imagens/altamira.jpg');
    $loginImageSources = site_image_sources($loginImage, 'hero');
    $authLogo = asset('imagens/logosemtur.png');
    $logoSources = site_image_sources($authLogo, 'logo');
@endphp
<div class="ui-auth-shell">
    <div class="ui-auth-card">
        <div class="ui-auth-media">
            <x-picture
                :jpg="$loginImageSources['jpg'] ?? $loginImage"
                :webp="$loginImageSources['webp'] ?? null"
                alt="Paisagem de Altamira"
                class="ui-auth-media-image"
                sizes="(max-width: 1024px) 100vw, 50vw"
                :width="$loginImageSources['width'] ?? null"
                :height="$loginImageSources['height'] ?? null"
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
                    <h1 class="ui-auth-heading">{{ ui_text('ui.auth.login_heading') }}</h1>
                    <p class="ui-auth-subtitle">{{ ui_text('ui.auth.login_subtitle') }}</p>
                </div>
            </div>
        </div>

        <div class="ui-auth-form-panel">
            <div>
                <div class="ui-auth-eyebrow">{{ ui_text('ui.auth.login_panel') }}</div>
                <h2 class="ui-auth-title">{{ ui_text('ui.auth.login_panel') }}</h2>
                <p class="ui-auth-copy">{{ ui_text('ui.auth.login_copy') }}</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="ui-auth-form" novalidate>
                @csrf

                @if(session('status'))
                    <div class="ui-alert ui-alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <a href="{{ route('google.redirect.localized', ['locale' => route_locale()]) }}" class="ui-auth-google" aria-label="{{ ui_text('ui.auth.google_login') }}">
                    <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.827 31.659 29.333 35 24 35c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.156 7.961 3.039l5.657-5.657C34.758 5.119 29.651 3 24 3 12.955 3 4 11.955 4 23s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.651-.389-3.917z"/>
                        <path fill="#FF3D00" d="M6.306 14.691l6.571 4.815C14.531 16.047 18.951 13 24 13c3.059 0 5.842 1.156 7.961 3.039l5.657-5.657C34.758 5.119 29.651 3 24 3 16.318 3 9.656 7.337 6.306 14.691z"/>
                        <path fill="#4CAF50" d="M24 43c5.267 0 10.049-2.019 13.682-5.318l-6.316-5.341C29.333 35 24.827 37 20 37c-5.285 0-9.747-3.39-11.367-8.108l-6.564 5.061C5.356 39.777 14.044 43 24 43z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-1.364 3.659-5.858 7-11.303 7-5.285 0-9.747-3.39-11.367-8.108l-6.564 5.061C5.356 39.777 14.044 43 24 43c11.045 0 20-8.955 20-20 0-1.341-.138-2.651-.389-3.917z"/>
                    </svg>
                    {{ ui_text('ui.auth.google_login') }}
                </a>

                <div class="ui-auth-divider"><span>{{ ui_text('ui.auth.or') }}</span></div>

                <div>
                    <label for="login" class="ui-form-label">{{ ui_text('ui.auth.email_or_cpf') }}</label>
                    <input
                        id="login"
                        name="login"
                        type="text"
                        autocomplete="username"
                        autocapitalize="off"
                        spellcheck="false"
                        value="{{ old('login') }}"
                        placeholder="seu@email.com ou 000.000.000-00"
                        class="ui-input js-login-mask"
                    >
                    @error('login')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="ui-form-label">{{ ui_text('ui.auth.password') }}</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="ui-input pr-12">
                        <button type="button" id="toggle-pass" class="ui-auth-password-toggle" aria-label="{{ ui_text('ui.auth.show_password') }}">
                            <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke-width="1.6"/>
                                <circle cx="12" cy="12" r="3" stroke-width="1.6"/>
                            </svg>
                            <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M3 3l18 18M10.6 10.6A3 3 0 0113.4 13.4M9.88 4.24A10.9 10.9 0 0112 4c7 0 11 8 11 8a18.6 18.6 0 01-5.09 5.82M6.12 6.12C2.78 8.17 1 12 1 12a18.6 18.6 0 005.4 5.92" stroke-width="1.6"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-between gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-[var(--ui-text-soft)]">
                        <input type="checkbox" name="remember" class="ui-form-check rounded">
                        {{ ui_text('ui.auth.remember') }}
                    </label>

                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-[var(--ui-primary)] hover:underline">{{ ui_text('ui.auth.forgot_password') }}</a>
                    @endif
                </div>

                <button type="submit" class="ui-btn-primary w-full justify-center">
                    {{ ui_text('ui.auth.login_panel') }}
                </button>

                @if(Route::has('register'))
                    <p class="text-center text-sm text-[var(--ui-text-soft)]">
                        {{ ui_text('ui.auth.no_account') }}
                        <a href="{{ route('register') }}" class="font-medium text-[var(--ui-primary)] hover:underline">{{ ui_text('ui.auth.create_account') }}</a>
                    </p>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var inputs = document.querySelectorAll('.js-login-mask');

        if (!inputs.length) {
            return;
        }

        function onlyDigits(value) {
            return (value || '').replace(/\D+/g, '');
        }

        function maskCpf(value) {
            var digits = onlyDigits(value).slice(0, 11);
            var output = '';

            for (var index = 0; index < digits.length; index++) {
                output += digits[index];

                if (index === 2 || index === 5) {
                    output += '.';
                }

                if (index === 8) {
                    output += '-';
                }
            }

            return output;
        }

        function looksLikeCpf(value) {
            return /^\d[\d.\-]*$/.test(value);
        }

        inputs.forEach(function (input) {
            input.addEventListener('input', function () {
                if (!looksLikeCpf(input.value)) {
                    return;
                }

                input.value = maskCpf(input.value);
            });

            if (looksLikeCpf(input.value)) {
                input.value = maskCpf(input.value);
            }
        });
    })();

    (function () {
        var button = document.getElementById('toggle-pass');
        var input = document.getElementById('password');
        var eyeOpen = document.getElementById('eye-open');
        var eyeClosed = document.getElementById('eye-closed');

        if (!button || !input || !eyeOpen || !eyeClosed) {
            return;
        }

        button.addEventListener('click', function () {
            var isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', !isPassword);
            eyeClosed.classList.toggle('hidden', isPassword);
        });
    })();
</script>
@endpush

@extends('site.layouts.app')

@section('title', __('ui.auth.register_title'))
@section('meta.noindex', 'true')

@section('site.content')
@php
    $registerImage = asset('imagens/altamira.jpg');
    $registerImageSources = site_image_sources($registerImage, 'hero');
    $logoSources = site_image_sources(theme_asset('logo'), 'logo');
@endphp
<div class="ui-auth-shell">
    <div class="ui-auth-card">
        <div class="ui-auth-media">
            <x-picture
                :jpg="$registerImageSources['jpg'] ?? $registerImage"
                :webp="$registerImageSources['webp'] ?? null"
                alt="Paisagem de Altamira"
                class="ui-auth-media-image"
                sizes="(max-width: 1024px) 100vw, 50vw"
                :width="$registerImageSources['width'] ?? null"
                :height="$registerImageSources['height'] ?? null"
                priority
            />
            <div class="ui-auth-media-overlay"></div>
            <div class="ui-auth-media-copy">
                <x-picture
                    :jpg="$logoSources['jpg'] ?? theme_asset('logo')"
                    :webp="$logoSources['webp'] ?? null"
                    alt="{{ __('ui.auth.logo_alt') }}"
                    class="ui-auth-media-logo"
                    sizes="180px"
                    :width="$logoSources['width'] ?? null"
                    :height="$logoSources['height'] ?? null"
                    priority
                />
                <div>
                    <div class="ui-auth-eyebrow">{{ __('ui.auth.eyebrow') }}</div>
                    <h1 class="ui-auth-heading">{{ __('ui.auth.register_heading') }}</h1>
                    <p class="ui-auth-subtitle">Cadastre-se para acessar experiências, mapas e novidades do destino.</p>
                </div>
            </div>
        </div>

        <div class="ui-auth-form-panel">
            <div>
                <div class="ui-auth-eyebrow">{{ __('ui.auth.register_panel') }}</div>
                <h2 class="ui-auth-title">{{ __('ui.auth.create_account') }}</h2>
                <p class="ui-auth-copy">Use e-mail ou CPF, defina sua senha ou continue com Google.</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="ui-auth-form" id="register-form" novalidate>
                @csrf

                <input type="hidden" name="email" id="reg_email" value="{{ old('email') }}">
                <input type="hidden" name="cpf" id="reg_cpf" value="{{ old('cpf') }}">

                @if ($errors->any())
                    <div class="ui-alert ui-alert-danger">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <a href="{{ route('google.redirect') }}" class="ui-auth-google" aria-label="{{ __('ui.auth.google_continue') }}">
                    <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.827 31.659 29.333 35 24 35c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.156 7.961 3.039l5.657-5.657C34.758 5.119 29.651 3 24 3 12.955 3 4 11.955 4 23s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.651-.389-3.917z"/>
                        <path fill="#FF3D00" d="M6.306 14.691l6.571 4.815C14.531 16.047 18.951 13 24 13c3.059 0 5.842 1.156 7.961 3.039l5.657-5.657C34.758 5.119 29.651 3 24 3 16.318 3 9.656 7.337 6.306 14.691z"/>
                        <path fill="#4CAF50" d="M24 43c5.267 0 10.049-2.019 13.682-5.318l-6.316-5.341C29.333 35 24.827 37 20 37c-5.285 0-9.747-3.39-11.367-8.108l-6.564 5.061C5.356 39.777 14.044 43 24 43z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-1.364 3.659-5.858 7-11.303 7-5.285 0-9.747-3.39-11.367-8.108l-6.564 5.061C5.356 39.777 14.044 43 24 43c11.045 0 20-8.955 20-20 0-1.341-.138-2.651-.389-3.917z"/>
                    </svg>
                    {{ __('ui.auth.google_continue') }}
                </a>

                <div class="ui-auth-divider"><span>{{ __('ui.auth.or') }}</span></div>

                <div>
                    <label for="name" class="ui-form-label">{{ __('ui.auth.name') }}</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        required
                        class="ui-input"
                    >
                    @error('name')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="identity" class="ui-form-label">{{ __('ui.auth.email_or_cpf') }}</label>
                    <input
                        id="identity"
                        name="identity"
                        type="text"
                        value="{{ old('email') ?: (old('cpf') ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', old('cpf')) : '') }}"
                        autocomplete="username"
                        autocapitalize="off"
                        spellcheck="false"
                        placeholder="seu@email.com ou 000.000.000-00"
                        class="ui-input js-register-identity"
                    >
                    @error('email')<p class="ui-form-error">{{ $message }}</p>@enderror
                    @error('cpf')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="ui-form-label">{{ __('ui.auth.password') }}</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" autocomplete="new-password" required class="ui-input pr-12">
                        <button type="button" data-auth-toggle="password" class="ui-auth-password-toggle" aria-label="{{ __('ui.auth.show_password') }}">
                            <svg data-auth-eye="open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke-width="1.6"/>
                                <circle cx="12" cy="12" r="3" stroke-width="1.6"/>
                            </svg>
                            <svg data-auth-eye="closed" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M3 3l18 18M10.6 10.6A3 3 0 0113.4 13.4M9.88 4.24A10.9 10.9 0 0112 4c7 0 11 8 11 8a18.6 18.6 0 01-5.09 5.82M6.12 6.12C2.78 8.17 1 12 1 12a18.6 18.6 0 005.4 5.92" stroke-width="1.6"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="ui-form-label">{{ __('ui.auth.confirm_password') }}</label>
                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="ui-input pr-12">
                        <button type="button" data-auth-toggle="password_confirmation" class="ui-auth-password-toggle" aria-label="Mostrar ou ocultar confirmação de senha">
                            <svg data-auth-eye="open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke-width="1.6"/>
                                <circle cx="12" cy="12" r="3" stroke-width="1.6"/>
                            </svg>
                            <svg data-auth-eye="closed" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M3 3l18 18M10.6 10.6A3 3 0 0113.4 13.4M9.88 4.24A10.9 10.9 0 0112 4c7 0 11 8 11 8a18.6 18.6 0 01-5.09 5.82M6.12 6.12C2.78 8.17 1 12 1 12a18.6 18.6 0 005.4 5.92" stroke-width="1.6"/>
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="ui-btn-primary w-full justify-center">
                    {{ __('ui.auth.create_account') }}
                </button>

                <p class="text-center text-sm text-[var(--ui-text-soft)]">
                    Já tem conta?
                    <a href="{{ route('login') }}" class="font-medium text-[var(--ui-primary)] hover:underline">Entrar</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var input = document.getElementById('identity');
        var hiddenEmail = document.getElementById('reg_email');
        var hiddenCpf = document.getElementById('reg_cpf');
        var form = document.getElementById('register-form');

        if (!input || !hiddenEmail || !hiddenCpf) {
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
            return /^\d[\d.\-]*$/.test(value || '');
        }

        function syncIdentity() {
            var raw = (input.value || '').trim();

            if (raw.indexOf('@') !== -1) {
                hiddenEmail.value = raw.toLowerCase();
                hiddenCpf.value = '';
                return;
            }

            hiddenCpf.value = onlyDigits(raw).slice(0, 11);
            hiddenEmail.value = '';
        }

        input.addEventListener('input', function () {
            if (looksLikeCpf(input.value)) {
                input.value = maskCpf(input.value);
            }

            syncIdentity();
        });

        input.addEventListener('paste', function () {
            window.setTimeout(function () {
                if (looksLikeCpf(input.value)) {
                    input.value = maskCpf(input.value);
                }

                syncIdentity();
            }, 0);
        });

        if (form) {
            form.addEventListener('submit', syncIdentity);
        }

        syncIdentity();
    })();

    (function () {
        var toggles = document.querySelectorAll('[data-auth-toggle]');

        if (!toggles.length) {
            return;
        }

        toggles.forEach(function (button) {
            var inputId = button.getAttribute('data-auth-toggle');
            var input = document.getElementById(inputId);
            var eyeOpen = button.querySelector('[data-auth-eye="open"]');
            var eyeClosed = button.querySelector('[data-auth-eye="closed"]');

            if (!input || !eyeOpen || !eyeClosed) {
                return;
            }

            button.addEventListener('click', function () {
                var isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                eyeOpen.classList.toggle('hidden', !isPassword);
                eyeClosed.classList.toggle('hidden', isPassword);
            });
        });
    })();
</script>
@endpush

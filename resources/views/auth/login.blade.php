{{-- resources/views/auth/login.blade.php --}}
@extends('site.layouts.app')
@section('title', 'Login — SEMTUR')

@push('head')
  <style>
  :root{ --brand:#00837B; }

  .ux-input{
    background:#E9E9E9!important; border-color:#E9E9E9!important;
    box-shadow:none!important;
  }
  .ux-input:focus{
    outline:none; box-shadow:0 0 0 2px rgba(0,131,123,.25)!important; border-color:#E9E9E9!important;
    background:#F3F3F3!important;
  }

  /* ===== Google CTA no estilo "Continue com Google" (branco, borda suave) ===== */
  .btn-google{
    display:inline-flex; align-items:center; justify-content:center;
    gap:.6rem; width:100%; height:44px;
    border:1px solid #dadce0; border-radius:9999px;  /* pílula */
    background:#fff; color:#3c4043; font-weight:600; font-size:15px;
    letter-spacing:.25px; line-height:1;
    transition:box-shadow .18s ease, background-color .18s ease, transform .02s;
  }
  .btn-google:hover{
    box-shadow:0 1px 2px rgba(60,64,67,.30), 0 1px 3px 1px rgba(60,64,67,.15);
  }
  .btn-google:focus{
    outline:none; box-shadow:0 0 0 3px rgba(26,115,232,.25);
  }
  .btn-google:active{ transform:translateY(.5px); background:#f7f8f8; }

  .or-divider{ display:flex; align-items:center; gap:.75rem; color:#64748b; font-size:.8rem; }
  .or-divider::before, .or-divider::after{ content:""; height:1px; flex:1; background:rgba(0,0,0,.1); }
</style>

@endpush

@section('site.content')
  <div class="mx-auto w-full max-w-[420px] md:max-w-[640px] lg:max-w-5xl px-4">

    {{-- ===== MOBILE/TABLET (até lg) ===== --}}
    <div class="block lg:hidden">
      {{-- Hero: imagem Altamira --}}
      <div class="relative h-[300px] md:h-[340px] -mt-6 overflow-hidden rounded-b-[24px]">
        <img src="{{ asset('imagens/altamira.jpg') }}" alt="Altamira" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-black/10 to-transparent"></div>
      </div>

      {{-- Cartão branco arredondado sobre a foto --}}
      <section class="relative -mt-6 bg-white rounded-t-[25px] shadow-md">
        <div class="px-5 pt-5 pb-2">
          <h1 class="text-[20px] font-bold text-[#202020]">Login</h1>
        </div>

        <form method="POST" action="{{ route('login') }}" id="login-form-m" class="px-5 pb-6 space-y-4" novalidate>
          @csrf
          @if (session('status'))
            <div class="text-xs rounded-lg p-2 bg-emerald-50 border border-emerald-200 text-emerald-700">
              {{ session('status') }}
            </div>
          @endif

          {{-- Entrar com Google --}}
          <a href="{{ route('google.redirect') }}" class="btn-google" aria-label="Continue com Google">
            <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.827 31.659 29.333 35 24 35c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.156 7.961 3.039l5.657-5.657C34.758 5.119 29.651 3 24 3 12.955 3 4 11.955 4 23s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.651-.389-3.917z"/>
                <path fill="#FF3D00" d="M6.306 14.691l6.571 4.815C14.531 16.047 18.951 13 24 13c3.059 0 5.842 1.156 7.961 3.039l5.657-5.657C34.758 5.119 29.651 3 24 3 16.318 3 9.656 7.337 6.306 14.691z"/>
                <path fill="#4CAF50" d="M24 43c5.267 0 10.049-2.019 13.682-5.318l-6.316-5.341C29.333 35 24.827 37 20 37c-5.285 0-9.747-3.39-11.367-8.108l-6.564 5.061C5.356 39.777 14.044 43 24 43z"/>
                <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-1.364 3.659-5.858 7-11.303 7-5.285 0-9.747-3.39-11.367-8.108l-6.564 5.061C5.356 39.777 14.044 43 24 43c11.045 0 20-8.955 20-20 0-1.341-.138-2.651-.389-3.917z"/>
            </svg>
            Continue com Google
         </a>


          <div class="or-divider"><span>ou</span></div>

          {{-- Email ou CPF --}}
          <div>
            <label for="login" class="block text-sm text-[#202020]">Email ou CPF</label>
            <input id="login" name="login" type="text" autocomplete="username"
                   autocapitalize="off" spellcheck="false"
                   value="{{ old('login') }}"
                   placeholder="seu@email.com ou 000.000.000-00"
                   class="ux-input mt-1 w-full rounded-md border px-3 py-2 text-[15px] js-login-mask">
            @error('login')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Senha --}}
          <div>
            <label for="password" class="block text-sm text-[#202020]">Senha</label>
            <div class="mt-1 relative">
              <input id="password" name="password" type="password" autocomplete="current-password" required
                     class="ux-input w-full rounded-md border px-3 py-2 text-[15px] pr-12">
              <button type="button" id="toggle-pass"
                      class="absolute inset-y-0 right-0 px-3 text-slate-500 hover:text-slate-700 focus:outline-none"
                      aria-label="Mostrar ou ocultar senha">
                {{-- olho aberto --}}
                <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke-width="1.6"/>
                  <circle cx="12" cy="12" r="3" stroke-width="1.6"/>
                </svg>
                {{-- olho fechado --}}
                <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M3 3l18 18M10.6 10.6A3 3 0 0113.4 13.4M9.88 4.24A10.9 10.9 0 0112 4c7 0 11 8 11 8a18.6 18.6 0 01-5.09 5.82M6.12 6.12C2.78 8.17 1 12 1 12a18.6 18.6 0 005.4 5.92" stroke-width="1.6"/>
                </svg>
              </button>
            </div>
            @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Ações --}}
          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-xs text-slate-600">
              <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
              Lembrar de mim
            </label>
            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="text-xs text-emerald-700 hover:underline underline-offset-4">
                Esqueci minha senha
              </a>
            @endif
          </div>

          {{-- Botão --}}
          <button type="submit"
                  class="mt-2 w-full h-[42px] inline-flex items-center justify-center rounded-md
                         bg-[var(--brand)] text-white text-[16px] font-bold hover:brightness-110 active:brightness-95">
            Login
          </button>

          {{-- Criar conta (sutil) --}}
          @if (Route::has('register'))
            <p class="text-center text-sm text-slate-600 mt-2">
              Não tem conta?
              <a href="{{ route('register') }}" class="text-emerald-700 font-medium hover:underline underline-offset-4">Criar conta</a>
            </p>
          @endif
        </form>
      </section>

      {{-- espaço pro bottom nav no mobile --}}
      <div class="h-[80px] md:hidden"></div>
      @includeIf('site.partials._bottom_nav')
    </div>

    {{-- ===== DESKTOP (lg+) — card em 2 colunas ===== --}}
    <div class="hidden lg:block">
      <section class="mx-auto mt-8 bg-white rounded-2xl shadow-[0_20px_40px_rgba(16,24,40,.12)] overflow-hidden">
        <div class="grid grid-cols-2">
          {{-- Coluna da imagem --}}
          <div class="relative">
            <img src="{{ asset('imagens/altamira.jpg') }}" alt="Altamira"
                 class="block w-full h-full object-cover">
            <div class="absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-black/15 to-transparent"></div>
          </div>

          {{-- Coluna do formulário --}}
          <div class="px-10 py-10">
            <h1 class="text-[24px] font-semibold text-slate-800">Login</h1>
            <p class="mt-1 text-sm text-slate-500">Acesse com e-mail ou CPF e sua senha.</p>

            <form method="POST" action="{{ route('login') }}" id="login-form-d" class="mt-6 space-y-5" novalidate>
              @csrf
              @if (session('status'))
                <div class="text-xs rounded-lg p-2 bg-emerald-50 border border-emerald-200 text-emerald-700">
                  {{ session('status') }}
                </div>
              @endif

              {{-- Entrar com Google --}}
              <a href="{{ route('google.redirect') }}" class="btn-google" aria-label="Entrar com Google">
                <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                  <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.827 31.659 29.333 35 24 35c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.156 7.961 3.039l5.657-5.657C34.758 5.119 29.651 3 24 3 12.955 3 4 11.955 4 23s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.651-.389-3.917z"/>
                  <path fill="#FF3D00" d="M6.306 14.691l6.571 4.815C14.531 16.047 18.951 13 24 13c3.059 0 5.842 1.156 7.961 3.039l5.657-5.657C34.758 5.119 29.651 3 24 3 16.318 3 9.656 7.337 6.306 14.691z"/>
                  <path fill="#4CAF50" d="M24 43c5.267 0 10.049-2.019 13.682-5.318l-6.316-5.341C29.333 35 24.827 37 20 37c-5.285 0-9.747-3.39-11.367-8.108l-6.564 5.061C5.356 39.777 14.044 43 24 43z"/>
                  <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-1.364 3.659-5.858 7-11.303 7-5.285 0-9.747-3.39-11.367-8.108l-6.564 5.061C5.356 39.777 14.044 43 24 43c11.045 0 20-8.955 20-20 0-1.341-.138-2.651-.389-3.917z"/>
                </svg>
                Entrar com Google
              </a>

              <div class="or-divider"><span>ou</span></div>

              <div>
                <label for="login_d" class="block text-sm font-medium text-slate-700">Email ou CPF</label>
                <input id="login_d" name="login" type="text" autocomplete="username"
                       autocapitalize="off" spellcheck="false"
                       value="{{ old('login') }}"
                       placeholder="seu@email.com ou 000.000.000-00"
                       class="ux-input mt-1 w-full rounded-md border px-3 py-2 text-[15px] js-login-mask">
                @error('login')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
              </div>

              <div>
                <label for="password_d" class="block text-sm font-medium text-slate-700">Senha</label>
                <div class="mt-1 relative">
                  <input id="password_d" name="password" type="password" autocomplete="current-password" required
                         class="ux-input w-full rounded-md border px-3 py-2 text-[15px] pr-12">
                  <button type="button" id="toggle-pass-d"
                          class="absolute inset-y-0 right-0 px-3 text-slate-500 hover:text-slate-700 focus:outline-none"
                          aria-label="Mostrar ou ocultar senha">
                    <svg id="eye-open-d" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke-width="1.6"/>
                      <circle cx="12" cy="12" r="3" stroke-width="1.6"/>
                    </svg>
                    <svg id="eye-closed-d" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M3 3l18 18M10.6 10.6A3 3 0 0113.4 13.4M9.88 4.24A10.9 10.9 0 0112 4c7 0 11 8 11 8a18.6 18.6 0 01-5.09 5.82M6.12 6.12C2.78 8.17 1 12 1 12a18.6 18.6 0 005.4 5.92" stroke-width="1.6"/>
                    </svg>
                  </button>
                </div>
                @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
              </div>

              <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                  <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  Lembrar de mim
                </label>
                @if (Route::has('password.request'))
                  <a href="{{ route('password.request') }}" class="text-xs text-emerald-700 hover:underline underline-offset-4">
                    Esqueci minha senha
                  </a>
                @endif
              </div>

              <button type="submit"
                      class="w-full h-[44px] inline-flex items-center justify-center rounded-md
                             bg-[var(--brand)] text-white text-[16px] font-semibold tracking-wide
                             hover:brightness-110 active:brightness-95">
                Login
              </button>

              @if (Route::has('register'))
                <p class="text-center text-sm text-slate-600">
                  Não tem conta?
                  <a href="{{ route('register') }}" class="text-emerald-700 font-medium hover:underline underline-offset-4">Criar conta</a>
                </p>
              @endif
            </form>
          </div>
        </div>
      </section>

      <div class="py-6"></div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    // Máscara CPF (visual)
    (function () {
      const inputs = document.querySelectorAll('.js-login-mask');
      if (!inputs.length) return;
      const onlyDigits = s => (s||'').replace(/\D+/g,'');
      const maskCpf = d => {
        const a = onlyDigits(d).slice(0,11);
        let out=''; for (let i=0;i<a.length;i++){ out+=a[i]; if(i===2||i===5) out+='.'; if(i===8) out+='-'; }
        return out;
      };
      const looksCpf = s => /^\d[\d.\-]*$/.test(s);
      inputs.forEach(el=>{
        el.addEventListener('input', () => {
          const raw = el.value;
          if (looksCpf(raw)) {
            el.value = maskCpf(raw);
            try { el.setSelectionRange(el.value.length, el.value.length); } catch(e){}
          }
        });
        if (looksCpf(el.value)) el.value = maskCpf(el.value);
      });
    })();

    // Mostrar/ocultar senha (mobile)
    (function () {
      const btn = document.getElementById('toggle-pass');
      const input = document.getElementById('password');
      const eyeOpen = document.getElementById('eye-open');
      const eyeClosed = document.getElementById('eye-closed');
      if (!btn || !input) return;
      btn.addEventListener('click', () => {
        const isPwd = input.type === 'password';
        input.type = isPwd ? 'text' : 'password';
        eyeOpen.classList.toggle('hidden', !isPwd);
        eyeClosed.classList.toggle('hidden', isPwd);
      });
    })();

    // Mostrar/ocultar senha (desktop)
    (function () {
      const btn = document.getElementById('toggle-pass-d');
      const input = document.getElementById('password_d');
      const eyeOpen = document.getElementById('eye-open-d');
      const eyeClosed = document.getElementById('eye-closed-d');
      if (!btn || !input) return;
      btn.addEventListener('click', () => {
        const isPwd = input.type === 'password';
        input.type = isPwd ? 'text' : 'password';
        eyeOpen.classList.toggle('hidden', !isPwd);
        eyeClosed.classList.toggle('hidden', isPwd);
      });
    })();
  </script>
@endpush

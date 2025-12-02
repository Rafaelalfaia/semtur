@extends('site.layouts.app')
@section('title', 'Criar conta — SEMTUR')

@push('head')
    <style>
    :root{ --brand:#00837B; }

    .ux-input{ background:#E9E9E9!important; border-color:#E9E9E9!important; box-shadow:none!important; }
    .ux-input:focus{ outline:none; box-shadow:0 0 0 2px rgba(0,131,123,.25)!important; border-color:#E9E9E9!important; background:#F3F3F3!important; }

    .or-divider{ display:flex; align-items:center; gap:.75rem; color:#64748b; font-size:.8rem; }
    .or-divider::before, .or-divider::after{ content:""; height:1px; flex:1; background:rgba(0,0,0,.1); }

    /* 👇 novo estilo “Continue com Google” (mesmo do login) */
    .btn-google{
        display:inline-flex; align-items:center; justify-content:center;
        gap:.6rem; width:100%; height:44px;
        border:1px solid #dadce0; border-radius:9999px;
        background:#fff; color:#3c4043; font-weight:600; font-size:15px;
        letter-spacing:.25px; line-height:1;
        transition:box-shadow .18s ease, background-color .18s ease, transform .02s;
    }
    .btn-google:hover{
        box-shadow:0 1px 2px rgba(60,64,67,.30), 0 1px 3px 1px rgba(60,64,67,.15);
    }
    .btn-google:focus{ outline:none; box-shadow:0 0 0 3px rgba(26,115,232,.25); }
    .btn-google:active{ transform:translateY(.5px); background:#f7f8f8; }
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
          <h1 class="text-[20px] font-bold text-[#202020]">Criar conta</h1>
        </div>

        <form method="POST" action="{{ route('register') }}" class="px-5 pb-6 space-y-4" id="register-form-m" novalidate>
          @csrf

          {{-- Campos ocultos que o back usa --}}
          <input type="hidden" name="email" id="reg_email_m" value="{{ old('email') }}">
          <input type="hidden" name="cpf"   id="reg_cpf_m"   value="{{ old('cpf')  }}">

          @if ($errors->any())
            <div class="text-xs rounded-lg p-2 bg-rose-50 border border-rose-200 text-rose-700">
              <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Nome --}}
          <div>
            <label for="name_m" class="block text-sm text-[#202020]">Nome</label>
            <input id="name_m" name="name" type="text" value="{{ old('name') }}" required
                   autocomplete="name"
                   class="ux-input mt-1 w-full rounded-md border px-3 py-2 text-[15px]">
            @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Email ou CPF (campo único) --}}
          <div>
            <label for="identity_m" class="block text-sm text-[#202020]">Email ou CPF</label>
            <input id="identity_m" name="identity" type="text"
                   value="{{ old('email') ?: (old('cpf') ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/','$1.$2.$3-$4', old('cpf')) : '') }}"
                   placeholder="seu@email.com ou 000.000.000-00"
                   inputmode="text" autocapitalize="off" spellcheck="false"
                   class="ux-input mt-1 w-full rounded-md border px-3 py-2 text-[15px] js-identity">
            @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            @error('cpf')  <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Senha --}}
          <div>
            <label for="password_m" class="block text-sm text-[#202020]">Senha</label>
            <input id="password_m" name="password" type="password" required
                   autocomplete="new-password"
                   class="ux-input mt-1 w-full rounded-md border px-3 py-2 text-[15px]">
            @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Confirmar senha --}}
          <div>
            <label for="password_confirmation_m" class="block text-sm text-[#202020]">Confirmar senha</label>
            <input id="password_confirmation_m" name="password_confirmation" type="password" required
                   autocomplete="new-password"
                   class="ux-input mt-1 w-full rounded-md border px-3 py-2 text-[15px]">
            @error('password_confirmation')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>

          {{-- Entrar com Google --}}
          <div class="or-divider mt-1"><span>ou</span></div>
          <a href="{{ route('google.redirect') }}" class="btn-google" aria-label="Continue com Google">
            <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.827 31.659 29.333 35 24 35c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.156 7.961 3.039l5.657-5.657C34.758 5.119 29.651 3 24 3 12.955 3 4 11.955 4 23s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.651-.389-3.917z"/>
                <path fill="#FF3D00" d="M6.306 14.691l6.571 4.815C14.531 16.047 18.951 13 24 13c3.059 0 5.842 1.156 7.961 3.039l5.657-5.657C34.758 5.119 29.651 3 24 3 16.318 3 9.656 7.337 6.306 14.691z"/>
                <path fill="#4CAF50" d="M24 43c5.267 0 10.049-2.019 13.682-5.318l-6.316-5.341C29.333 35 24.827 37 20 37c-5.285 0-9.747-3.39-11.367-8.108l-6.564 5.061C5.356 39.777 14.044 43 24 43z"/>
                <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-1.364 3.659-5.858 7-11.303 7-5.285 0-9.747-3.39-11.367-8.108l-6.564 5.061C5.356 39.777 14.044 43 24 43c11.045 0 20-8.955 20-20 0-1.341-.138-2.651-.389-3.917z"/>
            </svg>
            Continue com Google
            </a>


          {{-- Botão --}}
          <button type="submit"
                  class="mt-2 w-full h-[42px] inline-flex items-center justify-center rounded-md
                         bg-[var(--brand)] text-white text-[16px] font-bold hover:brightness-110 active:brightness-95">
            Criar conta
          </button>

          {{-- Já tem conta? --}}
          <p class="text-center text-sm text-slate-600 mt-2">
            Já tem conta?
            <a href="{{ route('login') }}" class="text-emerald-700 font-medium hover:underline underline-offset-4">Entrar</a>
          </p>
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
            <h1 class="text-[24px] font-semibold text-slate-800">Criar conta</h1>
            <p class="mt-1 text-sm text-slate-500">Cadastre-se com e-mail ou CPF e crie sua senha.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5" id="register-form-d" novalidate>
              @csrf
              <input type="hidden" name="email" id="reg_email_d" value="{{ old('email') }}">
              <input type="hidden" name="cpf"   id="reg_cpf_d"   value="{{ old('cpf')  }}">

              @if ($errors->any())
                <div class="text-xs rounded-lg p-2 bg-rose-50 border border-rose-200 text-rose-700">
                  <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif

              <div>
                <label for="name_d" class="block text-sm font-medium text-slate-700">Nome</label>
                <input id="name_d" name="name" type="text" value="{{ old('name') }}" required
                       autocomplete="name"
                       class="ux-input mt-1 w-full rounded-md border px-3 py-2 text-[15px]">
                @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
              </div>

              <div>
                <label for="identity_d" class="block text-sm font-medium text-slate-700">Email ou CPF</label>
                <input id="identity_d" name="identity" type="text"
                       value="{{ old('email') ?: (old('cpf') ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/','$1.$2.$3-$4', old('cpf')) : '') }}"
                       placeholder="seu@email.com ou 000.000.000-00"
                       inputmode="text" autocapitalize="off" spellcheck="false"
                       class="ux-input mt-1 w-full rounded-md border px-3 py-2 text-[15px] js-identity">
                @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                @error('cpf')  <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
              </div>

              <div>
                <label for="password_d" class="block text-sm font-medium text-slate-700">Senha</label>
                <input id="password_d" name="password" type="password" required
                       autocomplete="new-password"
                       class="ux-input mt-1 w-full rounded-md border px-3 py-2 text-[15px]">
                @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
              </div>

              <div>
                <label for="password_confirmation_d" class="block text-sm font-medium text-slate-700">Confirmar senha</label>
                <input id="password_confirmation_d" name="password_confirmation" type="password" required
                       autocomplete="new-password"
                       class="ux-input mt-1 w-full rounded-md border px-3 py-2 text-[15px]">
                @error('password_confirmation')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
              </div>

              <div class="or-divider"><span>ou</span></div>
              <a href="{{ route('google.redirect') }}" class="btn-google" aria-label="Entrar com Google">
                <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.827 31.659 29.333 35 24 35c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.156 7.961 3.039l5.657-5.657C34.758 5.119 29.651 3 24 3 12.955 3 4 11.955 4 23s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.651-.389-3.917z"/><path fill="#FF3D00" d="M6.306 14.691l6.571 4.815C14.531 16.047 18.951 13 24 13c3.059 0 5.842 1.156 7.961 3.039l5.657-5.657C34.758 5.119 29.651 3 24 3 16.318 3 9.656 7.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 43c5.267 0 10.049-2.019 13.682-5.318l-6.316-5.341C29.333 35 24.827 37 20 37c-5.285 0-9.747-3.39-11.367-8.108l-6.564 5.061C5.356 39.777 14.044 43 24 43z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-1.364 3.659-5.858 7-11.303 7-5.285 0-9.747-3.39-11.367-8.108l-6.564 5.061C5.356 39.777 14.044 43 24 43c11.045 0 20-8.955 20-20 0-1.341-.138-2.651-.389-3.917z"/></svg>
                Entrar com Google
              </a>

              <button type="submit"
                      class="w-full h-[44px] inline-flex items-center justify-center rounded-md
                             bg-[var(--brand)] text-white text-[16px] font-semibold tracking-wide
                             hover:brightness-110 active:brightness-95">
                Criar conta
              </button>

              <p class="text-center text-sm text-slate-600">
                Já tem conta?
                <a href="{{ route('login') }}" class="text-emerald-700 font-medium hover:underline underline-offset-4">Entrar</a>
              </p>
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
    // Identidade única (email ou CPF): detecta '@' = email; senão trata como CPF (com máscara).
    (function(){
      const onlyDigits = s => (s||'').replace(/\D+/g,'');
      const maskCpf = d => {
        const a = onlyDigits(d).slice(0,11);
        let out=''; for (let i=0;i<a.length;i++){ out+=a[i]; if(i===2||i===5) out+='.'; if(i===8) out+='-'; }
        return out;
      };
      const looksCpf = s => /^\d[\d.\-]*$/.test(s || '');

      function bindIdentity(inputId, hiddenEmailId, hiddenCpfId){
        const input = document.getElementById(inputId);
        const hEmail = document.getElementById(hiddenEmailId);
        const hCpf   = document.getElementById(hiddenCpfId);
        if(!input || !hEmail || !hCpf) return;

        // inicial: popular campo visível se veio old('cpf')
        if(hCpf.value && !input.value) input.value = maskCpf(hCpf.value);

        const sync = () => {
          const raw = (input.value || '').trim();
          if (raw.includes('@')) {
            hEmail.value = raw.toLowerCase();
            hCpf.value   = '';
          } else {
            const d = onlyDigits(raw).slice(0,11);
            hCpf.value   = d;
            hEmail.value = '';
          }
        };

        const onInput = () => {
          if (looksCpf(input.value)) {
            const posEnd = input.selectionEnd;
            input.value = maskCpf(input.value);
            try { input.setSelectionRange(input.value.length, input.value.length); } catch(e){}
          }
          sync();
        };

        input.addEventListener('input', onInput);
        input.addEventListener('paste', () => setTimeout(onInput, 0));

        const form = input.closest('form');
        if (form) form.addEventListener('submit', sync);

        // normaliza no load
        sync();
      }

      // mobile
      bindIdentity('identity_m','reg_email_m','reg_cpf_m');
      // desktop
      bindIdentity('identity_d','reg_email_d','reg_cpf_d');
    })();
  </script>
@endpush

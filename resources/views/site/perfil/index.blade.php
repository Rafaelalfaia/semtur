@extends('site.layouts.app')
@section('title','Minha Conta')

@section('site.content')
<div class="min-h-dvh bg-white text-slate-900">

  {{-- Top bar --}}
  <header class="sticky top-0 z-10 bg-white/80 backdrop-blur border-b border-slate-100">
    <div class="mx-auto max-w-lg px-4 py-3 flex items-center gap-3">
      @php
        $back = url()->previous();
        $safeBack = $back && $back !== url()->current() ? $back : route('site.home');
      @endphp
      <a href="{{ $safeBack }}"
         class="h-10 w-10 rounded-full bg-slate-100 grid place-items-center hover:bg-slate-200"
         aria-label="Voltar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <h1 class="text-lg sm:text-xl font-semibold">Minha Conta</h1>
    </div>
  </header>

  {{-- Toast de status --}}
  @if (session('status'))
    <div class="mx-auto max-w-lg px-4 pt-3">
      <div class="text-xs rounded-lg p-2 bg-emerald-50 border border-emerald-200 text-emerald-700">
        {{ session('status') }}
      </div>
    </div>
  @endif

  <main class="mx-auto max-w-lg pb-28">
    {{-- Avatar + identificação --}}
    <section class="px-6 pt-6 pb-2 flex flex-col items-center">
      <div class="relative h-24 w-24 rounded-full overflow-hidden bg-slate-200 ring-2 ring-slate-100">
        @php $foto = $u->avatar_url ?? null; @endphp
        @if($foto)
          <img src="{{ $foto }}" alt="{{ $u->name }}" class="h-full w-full object-cover">
        @else
          <div class="h-full w-full grid place-items-center text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5.33 0-8 2.67-8 6a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1c0-3.33-2.67-6-8-6Z"/>
            </svg>
          </div>
        @endif
      </div>

      <div class="mt-3 text-center">
        <div class="text-base sm:text-lg font-medium">{{ $u->name }}</div>
        <div class="text-sm text-slate-500">
          {{ $u->email ?: 'sem e-mail' }}
        </div>
        @if(!empty($u->cpf))
          <div class="mt-1 text-xs text-slate-400">
            @php
              $cpf = preg_replace('/\D+/','', (string)$u->cpf);
              $cpfMasked = strlen($cpf)===11 ? (substr($cpf,0,3).'.'.substr($cpf,3,3).'.'.substr($cpf,6,3).'-'.substr($cpf,9,2)) : $u->cpf;
            @endphp
            CPF: {{ $cpfMasked }}
          </div>
        @endif
      </div>
    </section>

    {{-- Menu de opções --}}
    <nav class="mt-4 bg-white">
      <ul class="divide-y divide-slate-200">
        <li>
          <a href="{{ route('site.perfil.editar') }}"
             class="flex items-center gap-3 px-6 py-4 active:bg-slate-50">
            <span class="text-teal-600 shrink-0">
              {{-- lápis --}}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 3.487a2 2 0 0 1 2.828 2.828l-10.9 10.9-4.29 1.43 1.43-4.29 10.9-10.9z"/>
              </svg>
            </span>
            <span class="flex-1 font-medium text-sm">Perfil</span>
            <svg class="h-4 w-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none">
              <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </a>
        </li>

        <li>
          <a href="{{ route('site.perfil.redes') }}"
             class="flex items-center gap-3 px-6 py-4 active:bg-slate-50">
            <span class="text-teal-600 shrink-0">
              {{-- globo --}}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 17.93V18a1 1 0 0 0-2 0v1.93A8.009 8.009 0 0 1 4.07 13H6a1 1 0 0 0 0-2H4.07A8.009 8.009 0 0 1 11 4.07V6a1 1 0 0 0 2 0V4.07A8.009 8.009 0 0 1 19.93 11H18a1 1 0 0 0 0 2h1.93A8.009 8.009 0 0 1 13 19.93Z"/>
              </svg>
            </span>
            <span class="flex-1 font-medium text-sm">Redes Sociais</span>
            <svg class="h-4 w-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none">
              <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </a>
        </li>

        <li>
          <a href="{{ route('site.politicas') }}"
             class="flex items-center gap-3 px-6 py-4 active:bg-slate-50">
            <span class="text-teal-600 shrink-0">
              {{-- cadeado --}}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17 8V7a5 5 0 0 0-10 0v1H5v13h14V8h-2Zm-8 0V7a3 3 0 0 1 6 0v1H9Z"/>
              </svg>
            </span>
            <span class="flex-1 font-medium text-sm">Política e Privacidade</span>
            <svg class="h-4 w-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none">
              <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </a>
        </li>
      </ul>
    </nav>

    {{-- Sair --}}
    <div class="px-6 mt-8">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
          class="w-full h-12 rounded-2xl border border-teal-500 text-teal-700 font-medium
                 hover:bg-teal-50 active:bg-teal-100 transition">
          Sair
        </button>
      </form>
    </div>
  </main>
</div>


{{-- Espaço p/ não cobrir conteúdo (mobile) + bottom nav --}}
<div class="h-[80px] pb-[env(safe-area-inset-bottom)] md:hidden"></div>
@includeIf('site.partials._bottom_nav')
@endsection

<!DOCTYPE html>
<html lang="pt-BR" class="antialiased">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>@yield('title','Console')</title>
  <meta name="theme-color" content="#0B0F0D">
  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('head')
</head>
<body
  x-data="{ menuOpen:false }"
  x-on:keydown.escape.window="menuOpen=false"
  :class="menuOpen ? 'overflow-hidden' : ''"
  class="bg-[#0B0F0D] text-slate-100 min-h-[100dvh]"
>
  {{-- Link de pular para conteúdo (acessibilidade) --}}
  <a href="#conteudo"
     class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-50 rounded-md bg-emerald-600 px-3 py-2 text-white">
    Ir para o conteúdo
  </a>

  <div class="grid min-h-[100dvh] lg:grid-cols-[260px_1fr]">
    {{-- SIDEBAR --}}
    <aside id="console-sidebar"
      class="fixed inset-y-0 left-0 z-40 w-[86%] max-w-[320px] -translate-x-full bg-[#0F1412]
             border-r border-white/10 px-4 py-4 transition-transform duration-200 ease-out
             lg:static lg:translate-x-0 lg:w-auto lg:h-[100dvh] lg:sticky lg:top-0 lg:overflow-y-auto"
      :class="menuOpen ? 'translate-x-0' : '-translate-x-full'"
      aria-label="Menu lateral"
    >
      {{-- Cabeçalho da sidebar --}}
      <div class="flex items-center justify-between gap-2 px-2">
        <div class="flex items-center gap-2">
          <div class="h-3 w-3 rounded-full bg-emerald-400"></div>
          <span class="font-semibold">SEMTUR Admin</span>
        </div>
        <button class="lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 hover:bg-white/10"
                @click="menuOpen=false" aria-label="Fechar menu">
          <svg width="20" height="20" fill="none"><path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="1.5"/></svg>
        </button>
      </div>

      @php
        // Ajuda a marcar ativo no menu
        $is = fn(string $pattern) => request()->routeIs($pattern);
        $linkBase = 'block rounded-lg px-3 py-2 transition hover:bg-white/10';
      @endphp

      {{-- Navegação --}}
        <nav class="mt-6 space-y-1">
        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}"
            class="{{ $linkBase }} {{ $is('admin.dashboard') ? 'bg-white/10' : '' }}">
            Dashboard
        </a>

        <div class="mt-4 mb-1 text-xs uppercase tracking-wide text-slate-400 px-2">Gestão</div>

        {{-- Usuários --}}
        <a href="{{ route('admin.usuarios.index') }}"
            class="{{ $linkBase }} {{ $is('admin.usuarios.*') ? 'bg-white/10' : '' }}">
            Usuários
        </a>

        {{-- Relatórios (fica clicável somente se a rota existir) --}}
        @if (\Illuminate\Support\Facades\Route::has('admin.relatorios.index'))
            <a href="{{ route('admin.relatorios.index') }}"
            class="{{ $linkBase }} {{ $is('admin.relatorios.*') ? 'bg-white/10' : '' }}">
            Relatórios
            </a>
        @else
            <span class="{{ $linkBase }} opacity-60 cursor-not-allowed">Relatórios</span>
        @endif

        <div class="mt-4 mb-1 text-xs uppercase tracking-wide text-slate-400 px-2">Conta</div>
        @php
            use Illuminate\Support\Str;
            $current = optional(request()->route())->getName();
            $role = Str::before((string) $current, '.'); // admin|coordenador|tecnico
            $perfilEdit = $role ? $role.'.config.perfil.edit' : null;
        @endphp

        @if ($perfilEdit && \Illuminate\Support\Facades\Route::has($perfilEdit))
            <a href="{{ route($perfilEdit) }}"
            class="{{ $linkBase }} {{ $is($role.'.config.perfil.*') ? 'bg-white/10' : '' }}">
            Configurações
            </a>
        @elseif (\Illuminate\Support\Facades\Route::has('profile.edit'))
            <a href="{{ route('profile.edit') }}"
            class="{{ $linkBase }} {{ $is('profile.*') ? 'bg-white/10' : '' }}">
            Configurações
            </a>
        @else
            <span class="{{ $linkBase }} opacity-60 cursor-not-allowed">Configurações</span>
        @endif
        </nav>


      {{-- Sessão do usuário --}}
      <div class="mt-8 border-t border-white/10 pt-4">
        <div class="text-xs text-slate-400">Sessão</div>
        <div class="mt-2 flex items-center gap-3">
          @php
            $user = auth()->user();
            $avatar = ($user && !empty($user->avatar_url)) ? $user->avatar_url : asset('imagens/avatar.png');
          @endphp
          <img src="{{ $avatar }}" class="h-9 w-9 rounded-full object-cover" alt="Foto">
          <div class="leading-tight">
            <div class="text-sm font-medium line-clamp-1">{{ $user?->name }}</div>
            <div class="text-xs text-slate-400 line-clamp-1">{{ $user?->email }}</div>
          </div>
        </div>

        @if (\Illuminate\Support\Facades\Route::has('logout'))
          <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="inline-flex text-sm text-slate-300 hover:text-emerald-400">Sair</button>
          </form>
        @else
          <a href="#" class="mt-3 inline-flex text-sm text-slate-300 hover:text-emerald-400">Sair</a>
        @endif
      </div>
    </aside>

    {{-- BACKDROP do drawer --}}
    <div x-show="menuOpen" x-transition.opacity
         class="fixed inset-0 z-30 bg-black/50 lg:hidden"
         @click="menuOpen=false" aria-hidden="true"></div>

    {{-- MAIN --}}
    <main class="relative p-4 sm:p-6 lg:p-8 overflow-x-hidden">
      {{-- Topbar --}}
      <div class="mb-4 flex items-center justify-between rounded-xl border border-white/10 bg-[#0F1412]
                  px-3 py-2 sm:px-4 sm:py-3">
        <div class="flex items-center gap-3">
          <button class="lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 hover:bg-white/10"
                  @click="menuOpen=true" aria-controls="console-sidebar" :aria-expanded="menuOpen" aria-label="Abrir menu">
            <svg width="20" height="20" fill="none"><path d="M3 6h14M3 10h14M3 14h14" stroke="currentColor" stroke-width="1.5"/></svg>
          </button>
          <div class="font-semibold">@yield('page.title','Dashboard')</div>
        </div>

        <div class="hidden xs:flex items-center gap-2 text-xs sm:text-sm">
          <span class="rounded-full bg-emerald-500/10 text-emerald-300 px-2.5 py-1">
            Ambiente: {{ app()->environment() }}
          </span>
          <span class="rounded-full bg-white/5 px-2.5 py-1">v1.0.0</span>
        </div>
      </div>

      {{-- Conteúdo --}}
      <div id="conteudo" class="pb-[env(safe-area-inset-bottom)]">
        @yield('content')
      </div>
    </main>
  </div>
</body>
</html>

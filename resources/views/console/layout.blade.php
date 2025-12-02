<!DOCTYPE html>
<html lang="pt-BR" class="antialiased" x-data x-bind:class="$store.console?.theme === 'light' ? 'light' : ''">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>@yield('title','Console')</title>
  <meta name="theme-color" content="#0B0F0D">
  <meta name="color-scheme" content="light dark">
  <style>
    :root { --bg:#0B0F0D; --panel:#0F1412; --panel-2:#111715; --text:#E5E7EB; --muted:#94A3B8; --line:rgba(255,255,255,.10); --brand:#E11D48; --brand-700:#BE123C; --chip:rgba(225,29,72,.10) }
    html.light { --bg:#FFFFFF; --panel:#FFFFFF; --panel-2:#F8FAFC; --text:#0F172A; --muted:#475569; --line:rgba(15,23,42,.10); --chip:rgba(225,29,72,.08) }
    html,body{background:var(--bg);color:var(--text)}
    .panel{background:var(--panel);border-color:var(--line)}
    .panel-2{background:var(--panel-2);border-color:var(--line)}
    .muted{color:var(--muted)}
    .chip{background:var(--chip)}
    .brand{background:var(--brand)}
    .brand:hover{background:var(--brand-700)}
    .link-muted{color:var(--muted)}
    .link-muted:hover{color:var(--text)}
  </style>
  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('head')
  <script>
    document.addEventListener('alpine:init', () => {
      const mq = matchMedia('(max-width: 1023.98px)');
      const themeMeta = document.querySelector('meta[name="theme-color"]');
      Alpine.store('console', {
        theme: localStorage.getItem('console.theme') || 'dark',
        menuOpen: JSON.parse(localStorage.getItem('console.menuOpen') || 'false'),
        lastMenuButton: null,
        isSmall: mq.matches,
        setTheme(v){
          this.theme = v;
          localStorage.setItem('console.theme', v);
          document.documentElement.classList.toggle('light', v === 'light');
          if (themeMeta) themeMeta.setAttribute('content', getComputedStyle(document.body).backgroundColor);
        },
        toggleTheme(){ this.setTheme(this.theme === 'light' ? 'dark' : 'light') },
        setMenu(v){
          if (!this.isSmall && v) v = false;
          this.menuOpen = v;
          localStorage.setItem('console.menuOpen', JSON.stringify(v));
        }
      });
      const store = Alpine.store('console');
      store.setTheme(store.theme);
      const sync = () => {
        store.isSmall = mq.matches;
        if (!store.isSmall && store.menuOpen) store.setMenu(false);
      };
      mq.addEventListener?.('change', sync);
      sync();
    });
  </script>
</head>
<body x-data x-on:keydown.escape.window="$store.console.setMenu(false)" :class="$store.console.menuOpen && $store.console.isSmall ? 'overflow-hidden' : ''" class="min-h-[100dvh]">
  <a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-50 rounded-md px-3 py-2 text-white" style="background:var(--brand)">Ir para o conteúdo</a>
  <div class="grid min-h-[100dvh] lg:grid-cols-[260px_1fr]">
    <aside id="console-sidebar" class="fixed inset-y-0 left-0 z-40 w-[86%] max-w-[320px] -translate-x-full panel border px-4 py-4 transition-transform duration-200 ease-out lg:static lg:translate-x-0 lg:w-auto lg:h-[100dvh] lg:sticky lg:top-0 lg:overflow-y-auto" :class="$store.console.menuOpen ? 'translate-x-0' : '-translate-x-full'" role="navigation" aria-label="Menu lateral" x-trap.noscroll.inert="$store.console.menuOpen && $store.console.isSmall">
      <div class="flex items-center justify-between gap-2 px-2">
        <div class="flex items-center gap-3">
          <div class="h-2.5 w-2.5 rounded-full" style="background:var(--brand)"></div>
          <span class="font-semibold">Trivento · Console</span>
        </div>
        <button class="lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg chip" @click="$store.console.setMenu(false)" aria-label="Fechar menu">
          <svg width="20" height="20" fill="none" class="muted"><path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="1.5"/></svg>
        </button>
      </div>

      @php
        use Illuminate\Support\Str;
        use Illuminate\Support\Facades\Route;
        $currentName  = optional(request()->route())->getName();
        $detectedRole = $currentName ? Str::before($currentName, '.') : null;
        $r = $role ?? ($detectedRole ?: 'coordenador');
        $is     = fn(string $pattern) => request()->routeIs($pattern);
        $hrefOf = fn(?string $name) => ($name && Route::has($name)) ? route($name) : null;
        $dashName = ["$r.dashboard","$r.home","dashboard","home"];
        $catName  = ["$r.categorias.index","coordenador.categorias.index"];
        $empName  = ["$r.empresas.index","coordenador.empresas.index"];
        $ptoName  = ["$r.pontos.index","$r.pontos_turisticos.index","coordenador.pontos.index","coordenador.pontos_turisticos.index"];
        $evtName  = ["$r.eventos.index","coordenador.eventos.index"];
        $banName  = ["$r.banners.index","coordenador.banners.index"];
        $banDName = ["$r.banners-destaque.index","$r.banners_destaque.index","coordenador.banners-destaque.index","coordenador.banners_destaque.index"];
        $secName  = ["$r.secretaria.index","coordenador.secretaria.index"];
        $relName  = ["$r.relatorios.index","coordenador.relatorios.index"];
        $cfgName  = ["$r.config.perfil.edit","$r.config.perfil","profile.edit","user.profile.edit"];
        $firstExisting = function(array $cands) { foreach($cands as $n){ if(Route::has($n)) return $n; } return null; };
        $links = [
          'dash' => $hrefOf($firstExisting($dashName)),
          'cat'  => $hrefOf($firstExisting($catName)),
          'emp'  => $hrefOf($firstExisting($empName)),
          'pto'  => $hrefOf($firstExisting($ptoName)),
          'evt'  => $hrefOf($firstExisting($evtName)),
          'ban'  => $hrefOf($firstExisting($banName)),
          'banD' => $hrefOf($firstExisting($banDName)),
          'sec'  => $hrefOf($firstExisting($secName)),
          'rel'  => $hrefOf($firstExisting($relName)),
          'cfg'  => $hrefOf($firstExisting($cfgName)),
        ];
        $routeActive = [
          'dash' => $is("$r.dashboard") || $is("$r.home") || $is('dashboard') || $is('home'),
          'cat'  => $is("$r.categorias.*") || $is('coordenador.categorias.*'),
          'emp'  => $is("$r.empresas.*")   || $is('coordenador.empresas.*'),
          'pto'  => $is("$r.pontos.*") || $is("$r.pontos_turisticos.*") || $is('coordenador.pontos.*') || $is('coordenador.pontos_turisticos.*'),
          'evt'  => $is("$r.eventos.*") || $is('coordenador.eventos.*'),
          'ban'  => $is("$r.banners.*") || $is('coordenador.banners.*'),
          'banD' => $is("$r.banners-destaque.*") || $is("$r.banners_destaque.*") || $is('coordenador.banners-destaque.*') || $is('coordenador.banners_destaque.*'),
          'sec'  => $is("$r.secretaria.*") || $is('coordenador.secretaria.*'),
          'rel'  => $is("$r.relatorios.*") || $is('coordenador.relatorios.*'),
          'cfg'  => $is("$r.config.*") || $is('*.config.*') || $is('profile.*') || $is('perfil.*'),
        ];
        $u = auth()->user();
        $hasConteudo = $u->can('categorias.view') || $u->can('empresas.view') || $u->can('pontos.view')
          || $u->canany(['banners.view','banners.manage'])
          || $u->canany(['banners_destaque.view','banners_destaque.manage'])
          || $u->canany(['avisos.view','avisos.manage'])
          || $u->canany(['eventos.view','eventos.manage','eventos.edicoes.manage','eventos.atrativos.manage','eventos.midias.manage'])
          || ($u->can('secretaria.edit') && $links['sec'])
          || $u->can('equipe.manage');
        $hasGestao = $u->can('relatorios.view') || $u->can('tecnicos.manage');
      @endphp

      <nav class="mt-6 space-y-1">
        @if($links['dash'])
          <x-console.navlink href="{{ $links['dash'] }}" :active="$routeActive['dash']">Dashboard</x-console.navlink>
        @endif

        @if($hasConteudo)
          <div class="mt-4 mb-1 text-[10px] uppercase tracking-wide muted px-2">Conteúdo</div>
        @endif

        @canany(['banners_destaque.view','banners_destaque.manage'])
          @if($links['banD'])
            <x-console.navlink href="{{ $links['banD'] }}" :active="$routeActive['banD']">Banner Principal</x-console.navlink>
          @endif
        @endcanany

        @canany(['banners.view','banners.manage'])
          @if($links['ban'])
            <x-console.navlink href="{{ $links['ban'] }}" :active="$routeActive['ban']">Banners</x-console.navlink>
          @endif
        @endcanany

        @canany(['avisos.view','avisos.manage'])
          @if (Route::has('coordenador.avisos.index'))
            <x-console.navlink href="{{ route('coordenador.avisos.index') }}" :active="request()->routeIs('coordenador.avisos.*')">Avisos</x-console.navlink>
          @endif
        @endcanany

        @can('categorias.view')
          @if($links['cat']) <x-console.navlink href="{{ $links['cat'] }}" :active="$routeActive['cat']">Categorias</x-console.navlink> @endif
        @endcan

        @can('empresas.view')
          @if($links['emp']) <x-console.navlink href="{{ $links['emp'] }}" :active="$routeActive['emp']">Empresas</x-console.navlink> @endif
        @endcan

        @can('pontos.view')
          @if($links['pto']) <x-console.navlink href="{{ $links['pto'] }}" :active="$routeActive['pto']">Pontos Turísticos</x-console.navlink> @endif
        @endcan

        @canany(['eventos.view','eventos.manage','eventos.edicoes.manage','eventos.atrativos.manage','eventos.midias.manage'])
          @if($links['evt']) <x-console.navlink href="{{ $links['evt'] }}" :active="$routeActive['evt']">Eventos</x-console.navlink> @endif
        @endcanany

        @can('secretaria.edit')
          @if($links['sec']) <x-console.navlink href="{{ $links['sec'] }}" :active="$routeActive['sec']">Secretaria</x-console.navlink> @endif
        @endcan

        @can('equipe.manage')
          @if (Route::has('coordenador.equipe.index'))
            <x-console.navlink href="{{ route('coordenador.equipe.index') }}" :active="request()->routeIs('coordenador.equipe.*')">Equipe</x-console.navlink>
          @endif
        @endcan

        @if($hasGestao)
          <div class="mt-4 mb-1 text-[10px] uppercase tracking-wide muted px-2">Gestão</div>
        @endif

        @can('tecnicos.manage')
          @if (Route::has('coordenador.tecnicos.index'))
            <x-console.navlink href="{{ route('coordenador.tecnicos.index') }}" :active="request()->routeIs('coordenador.tecnicos.*')">Técnicos</x-console.navlink>
          @endif
        @endcan

        @can('relatorios.view')
          @if($links['rel']) <x-console.navlink href="{{ $links['rel'] }}" :active="$routeActive['rel']">Relatórios</x-console.navlink> @endif
        @endcan

        <div class="mt-4 mb-1 text-[10px] uppercase tracking-wide muted px-2">Conta</div>
        @if($links['cfg'])
          <x-console.navlink href="{{ $links['cfg'] }}" :active="$routeActive['cfg']">Configurações</x-console.navlink>
        @endif
      </nav>

      <div class="mt-8 border-t" style="border-color:var(--line);">
        <div class="pt-4 text-xs muted">Sessão</div>
        @php
          $user   = auth()->user();
          $avatar = ($user && !empty($user->avatar_url)) ? $user->avatar_url : asset('images/avatar.png');
        @endphp
        <div class="mt-2 flex items-center gap-3">
          <img src="{{ $avatar }}" class="h-9 w-9 rounded-full object-cover" alt="Foto do usuário">
          <div class="leading-tight">
            <div class="text-sm font-medium line-clamp-1">{{ $user?->name }}</div>
            <div class="text-xs muted line-clamp-1">{{ $user?->email }}</div>
          </div>
        </div>

        <div class="mt-3 flex items-center gap-2">
          <button class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs chip" @click="$store.console.toggleTheme()" aria-label="Alternar tema">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 3a9 9 0 1 0 9 9 7 7 0 0 1-9-9Z" stroke="currentColor"/></svg>
            <span x-text="$store.console.theme==='light' ? 'Claro' : 'Escuro'"></span>
          </button>

          @if (Route::has('logout'))
            <form method="POST" action="{{ route('logout') }}" class="inline">
              @csrf
              <button type="submit" class="text-xs link-muted">Sair</button>
            </form>
          @endif
        </div>
      </div>
    </aside>

    <div x-show="$store.console.menuOpen && $store.console.isSmall" x-transition.opacity class="fixed inset-0 z-30 bg-black/50 lg:hidden" @click="$store.console.setMenu(false)" aria-hidden="true"></div>

    <main class="relative p-4 sm:p-6 lg:p-8 overflow-x-hidden" :inert="$store.console.menuOpen && $store.console.isSmall ? '' : null">
      <div class="mb-4 flex items-center justify-between rounded-xl border panel px-3 py-2 sm:px-4 sm:py-3">
        <div class="flex items-center gap-3">
          <button class="lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg chip" @click="$store.console.lastMenuButton = $el; $store.console.setMenu(true)" aria-controls="console-sidebar" :aria-expanded="$store.console.menuOpen ? 'true' : 'false'" aria-label="Abrir menu">
            <svg width="20" height="20" fill="none" class="muted"><path d="M3 6h14M3 10h14M3 14h14" stroke="currentColor" stroke-width="1.5"/></svg>
          </button>
          <div class="font-semibold">@yield('page.title','Dashboard')</div>
        </div>

        <div class="flex items-center gap-2">
          @if (request()->routeIs('coordenador.avisos.*') && Route::has('coordenador.avisos.create'))
            <a href="{{ route('coordenador.avisos.create') }}" class="hidden sm:inline-flex items-center rounded-lg px-3 py-2 text-sm text-white brand">+ Novo Aviso</a>
          @endif

          @if (request()->routeIs('coordenador.tecnicos.*') && Route::has('coordenador.tecnicos.create') && auth()->user()->can('tecnicos.manage'))
            <a href="{{ route('coordenador.tecnicos.create') }}" class="hidden sm:inline-flex items-center rounded-lg px-3 py-2 text-sm text-white brand">+ Novo Técnico</a>
          @endif

          @if(optional(auth()->user())->hasRole('Admin'))
            <div class="hidden xs:flex items-center gap-2 text-xs sm:text-sm">
              <span class="rounded-full px-2.5 py-1" style="background:var(--chip); color:var(--brand);">{{ strtoupper(app()->environment()) }}</span>
              <span class="rounded-full px-2.5 py-1" style="background:var(--line);">v1.0.0</span>
            </div>
          @endif
        </div>
      </div>

      @if (request()->routeIs('*dashboard*') || request()->routeIs('dashboard'))
        <div class="mx-auto w-full max-w-5xl">
          @includeIf('console.partials._banner_principal')
        </div>
      @endif

      <div id="conteudo" class="mx-auto w-full max-w-5xl pb-[env(safe-area-inset-bottom)]">
        <div class="rounded-2xl border panel-2 p-4 sm:p-6">
          @yield('content')
        </div>
      </div>
    </main>
  </div>

  @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="pt-BR" class="antialiased">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>@yield('title','Console')</title>
  <meta name="theme-color" content="#0B0F0D">
  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('head') {{-- permite que partials injetem scripts/css --}}
</head>
<body
  x-data="{
    menuOpen: JSON.parse(localStorage.getItem('console.menuOpen') || 'false')
  }"
  x-init="$watch('menuOpen', v => localStorage.setItem('console.menuOpen', JSON.stringify(v)))"
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
    {{-- SIDEBAR (drawer no mobile; sticky no desktop) --}}
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
          <span class="font-semibold">SEMTUR Console</span>
        </div>
        <button class="lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 hover:bg-white/10"
                @click="menuOpen=false" aria-label="Fechar menu">
          <svg width="20" height="20" fill="none"><path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="1.5"/></svg>
        </button>
      </div>

      @php
        use Illuminate\Support\Str;
        use Illuminate\Support\Facades\Route;

        // papel atual por prefixo do nome da rota
        $currentName  = optional(request()->route())->getName();
        $detectedRole = $currentName ? Str::before($currentName, '.') : null;
        $r = $role ?? ($detectedRole ?: 'coordenador');

        // helpers
        $is      = fn(string $pattern) => request()->routeIs($pattern);
        $hrefOf  = fn(?string $name) => $name ? route($name) : '#';
        $resolve = function (array $candidates): ?string {
          foreach ($candidates as $n) if ($n && Route::has($n)) return $n;
          return null;
        };

        // DASHBOARD
        $dashName   = $resolve(["$r.dashboard", 'dashboard', "$r.home", 'home']);
        $dashHref   = $hrefOf($dashName);
        $dashActive = $is("$r.dashboard") || $is('dashboard') || $is("$r.home") || $is('home');

        // CATEGORIAS
        $catIndexName = $resolve(["$r.categorias.index",'coordenador.categorias.index','categorias.index']);
        $catHref   = $hrefOf($catIndexName);
        $catActive = $is("$r.categorias.*") || $is('coordenador.categorias.*') || $is('categorias.*');

        // EMPRESAS
        $empIndexName = $resolve(["$r.empresas.index",'coordenador.empresas.index','empresas.index']);
        $empHref   = $hrefOf($empIndexName);
        $empActive = $is("$r.empresas.*") || $is('coordenador.empresas.*') || $is('empresas.*');

        // PONTOS TURÍSTICOS
        $ptoIndexName = $resolve([
          "$r.pontos.index",'coordenador.pontos.index','pontos.index',
          "$r.pontos_turisticos.index",'coordenador.pontos_turisticos.index','pontos_turisticos.index',
        ]);
        $ptoHref   = $hrefOf($ptoIndexName);
        $ptoActive = $is("$r.pontos.*") || $is('coordenador.pontos.*') || $is('pontos.*')
                  || $is("$r.pontos_turisticos.*") || $is('coordenador.pontos_turisticos.*') || $is('pontos_turisticos.*');

        // ===== EVENTOS (novo) =====
        $evtIndexName = $resolve([
          "$r.eventos.index", 'coordenador.eventos.index', 'eventos.index'
        ]);
        $evtHref   = $hrefOf($evtIndexName);
        // Ativo quando estiver em qualquer rota de eventos/edicoes/atrativos/midias
        $evtActive = $is("$r.eventos.*") || $is('coordenador.eventos.*') || $is('eventos.*')
                  || $is('coordenador.edicoes.*') || $is('coordenador.atrativos.*') || $is('coordenador.midias.*');

        // BANNERS (comuns)
        $banIndexName = $resolve([
          "$r.banners.index","$r.banner.index",
          'coordenador.banners.index','coordenador.banner.index',
          'banners.index','banner.index',
        ]) ?? collect(Route::getRoutes())->map(fn($rt)=>$rt->getName())->filter()->first(function($name) use ($r){
              return (Str::endsWith($name,'.banners.index') || Str::endsWith($name,'.banner.index'))
                     && (Str::startsWith($name,$r.'.')||Str::startsWith($name,'coordenador.'));
            }) ?? collect(Route::getRoutes())->map(fn($rt)=>$rt->getName())->filter()->first(fn($n)=>Str::endsWith($n,'.banners.index')||Str::endsWith($n,'.banner.index'));
        $banHref   = $hrefOf($banIndexName);
        $banActive = $is("$r.banners.*") || $is("$r.banner.*")
                  || $is('coordenador.banners.*') || $is('coordenador.banner.*')
                  || $is('*.banners.*') || $is('*.banner.*');

        // BANNERS DESTAQUE (banner principal)
        $banDestIndexName = $resolve([
          "$r.banners-destaque.index","$r.banner-destaque.index",
          "$r.banners_destaque.index","$r.banner_destaque.index",
          'coordenador.banners-destaque.index','coordenador.banner-destaque.index',
          'coordenador.banners_destaque.index','coordenador.banner_destaque.index',
          'banners-destaque.index','banner-destaque.index',
          'banners_destaque.index','banner_destaque.index',
        ]);

        if (!$banDestIndexName) {
          $names = collect(Route::getRoutes())->map(fn($rt)=>$rt->getName())->filter();
          $banDestIndexName =
            $names->first(function($n) use ($r){
              return (
                Str::endsWith($n,'.banners-destaque.index') ||
                Str::endsWith($n,'.banners_destaque.index') ||
                Str::endsWith($n,'.banner-destaque.index') ||
                Str::endsWith($n,'.banner_destaque.index')
              ) && (Str::startsWith($n,$r.'.') || Str::startsWith($n,'coordenador.'));
            }) ??
            $names->first(fn($n) =>
              Str::endsWith($n,'.banners-destaque.index') ||
              Str::endsWith($n,'.banners_destaque.index') ||
              Str::endsWith($n,'.banner-destaque.index') ||
              Str::endsWith($n,'.banner_destaque.index')
            );
        }

        $banDestHref   = $hrefOf($banDestIndexName);
        $banDestActive = $is("$r.banners-destaque.*") || $is("$r.banner-destaque.*")
                      || $is("$r.banners_destaque.*") || $is("$r.banner_destaque.*")
                      || $is('coordenador.banners-destaque.*') || $is('coordenador.banner-destaque.*')
                      || $is('coordenador.banners_destaque.*') || $is('coordenador.banner_destaque.*');

        $findSecretariaIndex = function (string $role) use ($resolve) {
      // tenta nomes comuns primeiro
      $candidates = [
        "$role.secretaria.index",
        'coordenador.secretaria.index',
        "$role.secretaria.home",
        'coordenador.secretaria.home',

        // ✅ adiciona o que você realmente tem:
        "$role.secretaria.edit",
        'coordenador.secretaria.edit',
        ];
      if ($name = $resolve($candidates)) {
          return $name;
      }

      // fallback: varre rotas nomeadas e pega a primeira de "secretaria" SEM parâmetros na URI
      $routes = collect(Route::getRoutes())->filter(function($rt) use ($role) {
    $name = $rt->getName();
    if (!$name) return false;

    $prefixOk = Str::startsWith($name, "$role.") || Str::startsWith($name, 'coordenador.');
    $isSecretaria = Str::contains($name, '.secretaria');
    $uriHasParams = Str::contains($rt->uri(), '{');

    // ✅ só GET
    $isGet = in_array('GET', $rt->methods(), true);

    return $prefixOk && $isSecretaria && !$uriHasParams && $isGet;
    })->map->getName()->values();

        return $routes->first(); // pode ser null
  };

        // ===== SECRETARIA (determinístico) =====
    $secIndexName = $resolve([
    "$r.secretaria.edit",
    'coordenador.secretaria.edit',
    "$r.secretaria.index",
    'coordenador.secretaria.index',
    "$r.secretaria.home",
    'coordenador.secretaria.home',
    ]);

    if (!$secIndexName) {
    // fallback: varre rotas nomeadas e pega a primeira de secretaria SEM parâmetros e SOMENTE GET
    $secIndexName = collect(Route::getRoutes())->first(function($rt) use ($r) {
        $name = $rt->getName();
        if (!$name) return false;

        $prefixOk     = Str::startsWith($name, "$r.") || Str::startsWith($name, 'coordenador.');
        $isSecretaria = Str::contains($name, '.secretaria.');
        $uriHasParams = Str::contains($rt->uri(), '{');
        $isGet        = in_array('GET', $rt->methods(), true);

        return $prefixOk && $isSecretaria && !$uriHasParams && $isGet;
    })?->getName();
    }

    $secActive = request()->routeIs("$r.secretaria.*")
            || request()->routeIs('coordenador.secretaria.*')
            || request()->is('coordenador/secretaria*');

    $secHref = $secIndexName
    ? route($secIndexName)
    : (Route::has('coordenador.secretaria.edit') ? route('coordenador.secretaria.edit') : url('/coordenador/secretaria'));
            // ===== RELATÓRIOS (Coordenador) — robusto =====
    $relIndexName = $resolve([
    "$r.relatorios.index",
    "$r.relatorios.home",
    'coordenador.relatorios.index',
    'coordenador.relatorios.home',
    // variações curtas
    "$r.relatorios",
    'coordenador.relatorios',
    // nome que usamos no controller anterior:
    'coord.relatorios.index',
    'coord.relatorios',
    // genérica:
    'relatorios.index',
    'relatorios.home',
    ]);

    if (! $relIndexName) {
  // varre TODAS as rotas nomeadas e pega a primeira que termine com ".relatorios.index" (ou ".relatorios")
  $all = collect(Route::getRoutes())->map(fn($rt) => $rt->getName())->filter();
  $relIndexName = $all->first(function($name) use ($r) {
    return $name
      && (Str::endsWith($name, '.relatorios.index') || Str::endsWith($name, '.relatorios'))
      && (
        Str::startsWith($name, $r . '.')          // ex.: coordenador.* / admin.* (seus papéis)
        || Str::startsWith($name, 'coordenador.') // prefixo comum
        || Str::startsWith($name, 'coord.')       // nosso caso: coord.relatorios.index
      );
  }) ?? $all->first(fn($name) => $name && (Str::endsWith($name, '.relatorios.index') || Str::endsWith($name, '.relatorios')));
}

$relActive = request()->routeIs("$r.relatorios.*")
          || request()->routeIs('coordenador.relatorios.*')
          || request()->routeIs('coord.relatorios.*')
          || request()->routeIs('relatorios.*');

// href seguro: se não achar rota indexável, mas você estiver dentro de Relatórios, usa a URL atual
$relHref = $relIndexName
  ? route($relIndexName)
  : ($relActive ? url()->current() : '#');

      @endphp

      {{-- Navegação --}}
        <nav class="mt-6 space-y-1">
        {{-- Dashboard (sempre visível) --}}
        <x-console.navlink href="{{ $dashHref }}" :active="$dashActive">Dashboard</x-console.navlink>

        @php
            $u = auth()->user();

            $hasConteudo =
            $u->can('categorias.view') ||
            $u->can('empresas.view')   ||
            $u->can('pontos.view')     ||
            $u->canany(['banners.view','banners.manage']) ||
            $u->canany(['banners_destaque.view','banners_destaque.manage']) ||
            $u->canany(['avisos.view','avisos.manage']) ||
            $u->canany(['eventos.view','eventos.manage','eventos.edicoes.manage','eventos.atrativos.manage','eventos.midias.manage']) ||
            ($u->can('secretaria.edit') && $secIndexName) ||
            $u->can('equipe.manage');
        @endphp

        @if($hasConteudo)
            <div class="mt-4 mb-1 text-xs uppercase tracking-wide text-slate-400 px-2">Conteúdo</div>
        @endif

        {{-- Banner Principal (destaques) --}}
        @canany(['banners_destaque.view','banners_destaque.manage'])
            @if($banDestIndexName)
            <x-console.navlink href="{{ $banDestHref }}" :active="$banDestActive">Banner Principal</x-console.navlink>
            @endif
        @endcanany

        {{-- Banners comuns --}}
        @canany(['banners.view','banners.manage'])
            @if($banIndexName)
            <x-console.navlink href="{{ $banHref }}" :active="$banActive">Banners</x-console.navlink>
            @endif
        @endcanany

        {{-- Avisos --}}
        @canany(['avisos.view','avisos.manage'])
            @if (Route::has('coordenador.avisos.index'))
            <x-console.navlink
                href="{{ route('coordenador.avisos.index') }}"
                :active="request()->routeIs('coordenador.avisos.*')"
            >
                Avisos
            </x-console.navlink>
            @endif
        @endcanany

        {{-- Categorias --}}
        @can('categorias.view')
            <x-console.navlink href="{{ $catHref }}" :active="$catActive">Categorias</x-console.navlink>
        @endcan

        {{-- Empresas --}}
        @can('empresas.view')
            <x-console.navlink href="{{ $empHref }}" :active="$empActive">Empresas</x-console.navlink>
        @endcan

        {{-- Pontos Turísticos --}}
        @can('pontos.view')
            <x-console.navlink href="{{ $ptoHref }}" :active="$ptoActive">Pontos Turísticos</x-console.navlink>
        @endcan

        {{-- Eventos --}}
        @canany(['eventos.view','eventos.manage','eventos.edicoes.manage','eventos.atrativos.manage','eventos.midias.manage'])
            @if($evtIndexName)
            <x-console.navlink href="{{ $evtHref }}" :active="$evtActive">Eventos</x-console.navlink>
            @endif
        @endcanany

        {{-- Secretaria (fixo + robusto) --}}
        @php
        $secUrl = \Illuminate\Support\Facades\Route::has('coordenador.secretaria.edit')
            ? route('coordenador.secretaria.edit')
            : url('/coordenador/secretaria'); // fallback mesmo sem nome de rota
        @endphp

        @if(auth()->user()?->hasRole('Coordenador') || auth()->user()?->can('secretaria.edit'))
        <x-console.navlink
            href="{{ $secUrl }}"
            :active="request()->routeIs('coordenador.secretaria.*') || request()->is('coordenador/secretaria*')"
        >
            Secretaria
        </x-console.navlink>
        @endif
        {{-- Equipe --}}
        @can('equipe.manage')
            @if (Route::has('coordenador.equipe.index'))
            <x-console.navlink href="{{ route('coordenador.equipe.index') }}" :active="request()->routeIs('coordenador.equipe.*')">Equipe</x-console.navlink>
            @endif
        @endcan

        {{-- Gestão --}}
        @php $hasGestao = $u->can('relatorios.view') || $u->can('tecnicos.manage'); @endphp
        @if($hasGestao)
        <div class="mt-4 mb-1 text-xs uppercase tracking-wide text-slate-400 px-2">Gestão</div>
        @endif

        {{-- Técnicos (gestão interna pelo coordenador) --}}
        @can('tecnicos.manage')
        @if (Route::has('coordenador.tecnicos.index'))
            <x-console.navlink
            href="{{ route('coordenador.tecnicos.index') }}"
            :active="request()->routeIs('coordenador.tecnicos.*')">
            Técnicos
            </x-console.navlink>
        @endif
        @endcan

        {{-- Relatórios --}}
        @can('relatorios.view')
        @if($relIndexName)
            <x-console.navlink href="{{ $relHref }}" :active="$relActive">Relatórios</x-console.navlink>
        @endif
        @endcan

        {{-- Conta / Configurações (sempre visível se a rota existir) --}}
        @php
            $cfgName   = $resolve(["$r.config.perfil.edit","$r.config.perfil","$r.perfil.edit",'profile.edit','user.profile.edit']);
            $cfgHref   = $hrefOf($cfgName);
            $cfgActive = $is("$r.config.*") || $is('*.config.*') || $is('profile.*') || $is('perfil.*');
        @endphp
        <div class="mt-4 mb-1 text-xs uppercase tracking-wide text-slate-400 px-2">Conta</div>
        <x-console.navlink href="{{ $cfgHref }}" :active="$cfgActive">Configurações</x-console.navlink>
        </nav>


      {{-- Sessão do usuário --}}
      <div class="mt-8 border-t border-white/10 pt-4">
        <div class="text-xs text-slate-400">Sessão</div>
        @php
          $user   = auth()->user();
          $avatar = ($user && !empty($user->avatar_url)) ? $user->avatar_url : asset('imagens/avatar.png');
        @endphp
        <div class="mt-2 flex items-center gap-3">
          <img src="{{ $avatar }}" class="h-9 w-9 rounded-full object-cover" alt="Foto">
          <div class="leading-tight">
            <div class="text-sm font-medium line-clamp-1">{{ $user?->name }}</div>
            <div class="text-xs text-slate-400 line-clamp-1">{{ $user?->email }}</div>
          </div>
        </div>

        @if (Route::has('logout'))
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
                  @click="menuOpen=true" aria-controls="console-sidebar" :aria-expanded="menuOpen ? 'true' : 'false'" aria-label="Abrir menu">
            <svg width="20" height="20" fill="none"><path d="M3 6h14M3 10h14M3 14h14" stroke="currentColor" stroke-width="1.5"/></svg>
          </button>
          <div class="font-semibold">@yield('page.title','Dashboard')</div>
        </div>

        <div class="flex items-center gap-2">
          {{-- Atalho: + Novo Aviso quando dentro de Avisos --}}
          @if (request()->routeIs('coordenador.avisos.*') && Route::has('coordenador.avisos.create'))
            <a href="{{ route('coordenador.avisos.create') }}"
               class="hidden sm:inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-sm text-white hover:bg-emerald-700">
              + Novo Aviso
            </a>
          @endif

          {{-- Atalho: + Novo Técnico quando dentro de Técnicos --}}
            @if (request()->routeIs('coordenador.tecnicos.*') && Route::has('coordenador.tecnicos.create') && auth()->user()->can('tecnicos.manage'))
            <a href="{{ route('coordenador.tecnicos.create') }}"
                class="hidden sm:inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-sm text-white hover:bg-emerald-700">
                + Novo Técnico
            </a>
            @endif


          <div class="hidden xs:flex items-center gap-2 text-xs sm:text-sm">
            <span class="rounded-full bg-emerald-500/10 text-emerald-300 px-2.5 py-1">
              Ambiente: {{ app()->environment() }}
            </span>
            <span class="rounded-full bg-white/5 px-2.5 py-1">v1.0.0</span>
          </div>
        </div>
      </div>

      {{-- Banner Principal (no dashboard) --}}
      @if (request()->routeIs('*dashboard*') || request()->routeIs('dashboard'))
        @includeIf('console.partials._banner_principal')
      @endif

      {{-- Conteúdo --}}
      <div id="conteudo" class="pb-[env(safe-area-inset-bottom)]">
        @yield('content')
      </div>
    </main>
  </div>
  @stack('scripts')
</body>
</html>

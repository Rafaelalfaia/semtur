@php
use Illuminate\Support\Facades\Route as R;
use Illuminate\Support\Facades\Auth;

/* --------- LINKS SEGUROS --------- */
$homeHref     = R::has('site.home')     ? route('site.home')     : url('/');
$explorarHref = R::has('site.explorar') ? route('site.explorar') : url('/explorar');
$semturHref   = R::has('site.semtur')   ? route('site.semtur')   : url('/semtur'); // 👈

/* Mapa (substitui “Ingressos”) */
$mapHref = null;
foreach (['site.mapa','mapa.index','site.explorar.mapa'] as $r) {
  if (R::has($r)) { $mapHref = route($r); break; }
}
$mapHref = $mapHref ?: url('/mapa');

/* Perfil / Entrar com roles */
$u = Auth::user();
$perfilLabel = 'Entrar';
$perfilHref  = R::has('login') ? route('login') : url('/login');

if ($u) {
  $perfilLabel = 'Perfil';
  $role = null;
  if (method_exists($u, 'hasRole')) {
    foreach (['admin','gestor','coordenador','cidadao'] as $r) if ($u->hasRole($r)) { $role = $r; break; }
  } elseif (isset($u->role) && is_string($u->role)) {
    $role = strtolower($u->role);
  }

  $prefer = [
    'cidadao'     => ['cidadao.perfil','cidadao.dashboard','site.perfil'],
    'coordenador' => ['coordenador.dashboard','coordenador.home'],
    'gestor'      => ['gestor.dashboard','gestor.home'],
    'admin'       => ['admin.dashboard','admin.home'],
  ];
  if ($role && $role !== 'cidadao') $perfilLabel = 'Painel';

  $dest = null;
  if ($role && isset($prefer[$role])) {
    foreach ($prefer[$role] as $r) if (R::has($r)) { $dest = route($r); break; }
  }
  if (!$dest) {
    foreach (['site.perfil','profile.show','dashboard','home'] as $r) if (R::has($r)) { $dest = route($r); break; }
  }
  $perfilHref = $dest ?: $homeHref;
}

/* --------- TABS --------- */
$tabs = [
  ['key'=>'home',     'href'=>$homeHref,     'label'=>'Início',   'icon'=>'home'],
  ['key'=>'map',      'href'=>$mapHref,      'label'=>'Mapa',     'icon'=>'map'],
  ['key'=>'discover', 'href'=>$explorarHref, 'label'=>'Explorar', 'icon'=>'compass'],
  ['key'=>'semtur', 'href'=>$semturHref, 'label'=>'SEMTUR',   'icon'=>'building'], // 👈
  ['key'=>'perfil',   'href'=>$perfilHref,   'label'=>$perfilLabel,'icon'=>'user'],
];

/* --------- ATIVO --------- */
$isActive = function(string $key){
  return match(true){
    $key==='home'     => request()->routeIs('site.home'),
    $key==='map'      => request()->routeIs('site.mapa*') || request()->is('mapa*'),
    $key==='discover' => request()->routeIs('site.explorar*') || request()->is('explorar*'),
    $key==='semtur'   => request()->routeIs('site.semtur') || request()->is('semtur*'), // 👈
    $key==='perfil'   => request()->routeIs([
                        'site.perfil*','profile*','dashboard',
                        'admin.*','gestor.*','coordenador.*','cidadao.*','login'
                      ]),
    default => false
  };
};
@endphp

<header class="hidden md:block sticky top-0 z-50 bg-white border-b border-slate-200/60">
      <div class="mx-auto w-full max-w-[1200px] px-6 h-16 flex items-center justify-between">
    <a href="{{ $homeHref }}" class="flex items-center gap-3 group">
      <img src="/imagens/visitpreto.png" alt="SEMTUR" class="h-7 opacity-80 group-hover:opacity-100 transition">
      <span class="text-slate-700 font-semibold">Descubra Altamira</span>
    </a>

    <nav class="flex items-center gap-1">
      @foreach($tabs as $t)
        @php $active = $isActive($t['key']); @endphp
        <a href="{{ $t['href'] }}"
           class="px-3 py-2 rounded-lg text-sm flex items-center gap-2
                  {{ $active ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100' }}">
          @switch($t['icon'])
            @case('home')
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M3 10l9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10z"/>
              </svg>
            @break

            @case('map') {{-- pino/mapa --}}
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M9 20l-5 2V6l5-2 6 2 5-2v16l-5 2-6-2z"/><circle cx="15" cy="9" r="2.5"/>
              </svg>
            @break

            @case('compass')
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="10"/><path d="M9 15l6-3-3 6-3-3z"/>
              </svg>
            @break

            @case('building')
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M3 21h18M6 21V9l6-4 6 4v12M9 21v-6m6 6v-6"/>
              </svg>
            @break

            @case('user')
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/>
              </svg>
            @break
          @endswitch
          <span>{{ $t['label'] }}</span>
        </a>
      @endforeach
    </nav>
  </div>
</header>

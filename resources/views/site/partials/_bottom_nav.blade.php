@php
use Illuminate\Support\Facades\Route as R;
use Illuminate\Support\Facades\Auth;

/* ------- URLs seguras ------- */
$homeHref     = R::has('site.home')     ? route('site.home')     : url('/');
$explorarHref = R::has('site.explorar') ? route('site.explorar') : url('/explorar');
$orgaosHref   = R::has('site.orgaos')   ? route('site.orgaos')   : url('/orgaos');

/* Mapa (substitui “Ingressos”) */
$mapHref = null;
foreach (['site.mapa','mapa.index','site.explorar.mapa'] as $r) {
  if (R::has($r)) { $mapHref = route($r); break; }
}
$mapHref = $mapHref ?: (url('/mapa') ?: $homeHref);

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

$semturHref   = R::has('site.semtur') ? route('site.semtur') : url('/semtur'); // 👈

/* Tabs */
$tabs = [
  ['key'=>'home',      'href'=>$homeHref,     'label'=>'Início',   'icon'=>'home'],
  ['key'=>'map',       'href'=>$mapHref,      'label'=>'Mapa',     'icon'=>'map'],
  ['key'=>'explorar',  'href'=>$explorarHref, 'label'=>'Explorar', 'icon'=>'compass'],
  ['key'=>'semtur',    'href'=>$semturHref,  'label'=>'SEMTUR',   'icon'=>'building'], // 👈
  ['key'=>'perfil',    'href'=>$perfilHref,   'label'=>$perfilLabel,'icon'=>'user'],
];

/* Aba ativa */
$activeKey = 'home';
if (request()->routeIs('site.mapa*') || request()->is('mapa*'))                            $activeKey='map';
elseif (request()->routeIs('site.explorar*') || request()->is('explorar*'))                $activeKey='explorar';
elseif (request()->routeIs('site.semtur')   || request()->is('semtur*'))      $activeKey='semtur'; // 👈
elseif (request()->routeIs(['site.perfil*','profile*','dashboard','admin.*','gestor.*',
                            'coordenador.*','cidadao.*','login']))                         $activeKey='perfil';
@endphp

<div class="fixed inset-x-0 bottom-0 z-50 md:hidden
            pb-[calc(env(safe-area-inset-bottom,0)+8px)] pt-2
            bg-transparent pointer-events-none" role="navigation" aria-label="Menu inferior">

  <nav class="pointer-events-auto mx-auto w-full max-w-[480px] px-3">
    <div class="relative mx-auto max-w-[420px] rounded-full bg-[#00837B]
                h-16 px-3 flex items-center justify-between
                shadow-[0_8px_24px_rgba(0,0,0,0.18)]">

      @foreach($tabs as $t)
        @php $active = $t['key'] === $activeKey; @endphp
        <a href="{{ $t['href'] }}"
           class="relative grid place-items-center w-12 h-12 {{ $active ? 'bg-white rounded-full' : '' }}"
           aria-label="{{ $t['label'] }}" @if($active) aria-current="page" @endif>

          @switch($t['icon'])
            @case('home')
              @if($active)
                <svg class="w-6 h-6 text-[#00837B]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3.1 3 10v10h6v-6h6v6h6V10z"/></svg>
              @else
                <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10l9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10z"/></svg>
              @endif
            @break

            @case('map') {{-- ícone de mapa/pino --}}
              <svg class="w-6 h-6 {{ $active ? 'text-[#00837B]' : 'text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M9 20l-5 2V6l5-2 6 2 5-2v16l-5 2-6-2z"/><circle cx="15" cy="9" r="2.5"/>
              </svg>
            @break

            @case('compass')
              <svg class="w-6 h-6 {{ $active ? 'text-[#00837B]' : 'text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="10"/><path d="M9 15l6-3-3 6-3-3z"/>
              </svg>
            @break

            @case('building')
              <svg class="w-6 h-6 {{ $active ? 'text-[#00837B]' : 'text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M3 21h18M6 21V9l6-4 6 4v12M9 21v-6m6 6v-6"/>
              </svg>
            @break

            @case('user')
              <svg class="w-6 h-6 {{ $active ? 'text-[#00837B]' : 'text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/>
              </svg>
            @break
          @endswitch
        </a>
      @endforeach
    </div>

    <div class="mx-auto mt-2 h-1.5 w-36 rounded-full bg-[#C3C5C8]"></div>
  </nav>
</div>

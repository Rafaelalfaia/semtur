@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route as R;

    $homeHref = R::has('site.home') ? route('site.home') : url('/');
    $descubraHref = R::has('site.explorar') ? route('site.explorar') : (R::has('site.descubra') ? route('site.descubra') : url('/descubra-altamira'));
    $agendaHref = R::has('site.agenda') ? route('site.agenda') : url('/agenda');
    $mapaHref = R::has('site.mapa') ? route('site.mapa') : url('/mapa');

    $perfilHref = R::has('login') ? route('login') : url('/login');
    $u = Auth::user();

    if ($u && method_exists($u, 'hasRole')) {
        if ($u->hasRole('Admin') && R::has('admin.dashboard')) {
            $perfilHref = route('admin.dashboard');
        } elseif ($u->hasRole('Coordenador') && R::has('coordenador.dashboard')) {
            $perfilHref = route('coordenador.dashboard');
        } elseif ($u->hasRole('Cidadao') && R::has('site.perfil.index')) {
            $perfilHref = route('site.perfil.index');
        } elseif (R::has('dashboard')) {
            $perfilHref = route('dashboard');
        }
    }

    $perfilLabel = $u ? 'Perfil' : 'Entrar';
    $iconFor = function (string $key): string {
        return match ($key) {
            'home' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 10.5 12 4l8 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.5 9.5V20h11V9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'descubra' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="M16 16 20 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
            'agenda' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="5.5" width="16" height="14" rx="3" stroke="currentColor" stroke-width="1.8"/><path d="M8 3.5v4M16 3.5v4M4 9.5h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
            'mapa' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 18.5 4.5 20V5.5L9 4l6 1.5 4.5-1.5V18.5L15 20 9 18.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 4v14.5M15 5.5V20" stroke="currentColor" stroke-width="1.8"/></svg>',
            default => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.8"/><path d="M5.5 19a6.5 6.5 0 0 1 13 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
        };
    };

    $tabs = [
        ['key' => 'home', 'href' => $homeHref, 'label' => 'Início'],
        ['key' => 'descubra', 'href' => $descubraHref, 'label' => 'Descubra'],
        ['key' => 'agenda', 'href' => $agendaHref, 'label' => 'Agenda'],
        ['key' => 'mapa', 'href' => $mapaHref, 'label' => 'Mapa'],
        ['key' => 'perfil', 'href' => $perfilHref, 'label' => $perfilLabel],
    ];

    $activeKey = 'home';
    if (request()->routeIs('site.descubra') || request()->routeIs('site.explorar*')) {
        $activeKey = 'descubra';
    } elseif (request()->routeIs('site.agenda') || request()->routeIs('eventos.*')) {
        $activeKey = 'agenda';
    } elseif (request()->routeIs('site.mapa*')) {
        $activeKey = 'mapa';
    } elseif (request()->routeIs(['site.perfil*', 'profile*', 'dashboard', 'admin.*', 'coordenador.*', 'login'])) {
        $activeKey = 'perfil';
    }
@endphp

<div class="site-bottom-nav-shell md:hidden">
    <nav class="site-bottom-nav" aria-label="Menu público inferior">
        @foreach($tabs as $tab)
            @php $active = $tab['key'] === $activeKey; @endphp
            <a href="{{ $tab['href'] }}"
               class="{{ $active ? 'site-bottom-nav-link is-active' : 'site-bottom-nav-link' }}"
               aria-label="{{ $tab['label'] }}"
               @if($active) aria-current="page" @endif>
                <span class="site-bottom-nav-icon" aria-hidden="true">{!! $iconFor($tab['key']) !!}</span>
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>

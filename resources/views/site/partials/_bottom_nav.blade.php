@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route as R;

    $routeUrl = function (string $name, string $fallback = '#') {
        return R::has($name) ? route($name) : $fallback;
    };

    $perfilHref = R::has('login') ? route('login') : url('/login');
    $perfilLabel = 'Entrar';

    $u = Auth::user();
    if ($u) {
        $perfilLabel = 'Perfil';

        if (method_exists($u, 'hasRole')) {
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
    }

    $iconFor = function (string $key): string {
        return match ($key) {
            'home' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 10.5 12 4l8 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.5 9.5V20h11V9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'explorar' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="M16 16 20 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
            'agenda' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="5.5" width="16" height="14" rx="3" stroke="currentColor" stroke-width="1.8"/><path d="M8 3.5v4M16 3.5v4M4 9.5h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
            'mapa' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 18.5 4.5 20V5.5L9 4l6 1.5 4.5-1.5V18.5L15 20 9 18.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 4v14.5M15 5.5V20" stroke="currentColor" stroke-width="1.8"/></svg>',
            'perfil' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.8"/><path d="M5.5 19a6.5 6.5 0 0 1 13 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
            default => '',
        };
    };

    $tabs = collect([
        [
            'key' => 'home',
            'label' => json_decode('"In\u00edcio"'),
            'href' => $routeUrl('site.home', url('/')),
            'match' => ['site.home'],
        ],
        [
            'key' => 'explorar',
            'label' => 'Explorar',
            'href' => $routeUrl('site.explorar'),
            'match' => ['site.explorar*'],
        ],
        [
            'key' => 'agenda',
            'label' => 'Agenda',
            'href' => $routeUrl('site.agenda'),
            'match' => ['site.agenda', 'eventos.*'],
        ],
        [
            'key' => 'mapa',
            'label' => 'Mapa',
            'href' => $routeUrl('site.mapa'),
            'match' => ['site.mapa*'],
        ],
        [
            'key' => 'perfil',
            'label' => $perfilLabel,
            'href' => $perfilHref,
            'match' => ['site.perfil*', 'profile*', 'dashboard', 'admin.*', 'coordenador.*', 'login'],
        ],
    ])->filter(fn ($item) => filled($item['href']) && $item['href'] !== '#')->values();

    $navLabel = json_decode('"Menu p\u00fablico inferior"');
@endphp

<div class="site-bottom-nav-shell lg:hidden">
    <nav class="site-bottom-nav" aria-label="{{ $navLabel }}">
        @foreach($tabs as $tab)
            @php $active = request()->routeIs($tab['match']); @endphp
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

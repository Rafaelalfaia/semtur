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

    $sections = collect([
        [
            'label' => json_decode('"In\u00edcio"'),
            'href' => $routeUrl('site.home', url('/')),
            'match' => ['site.home'],
        ],
        [
            'label' => 'Explorar',
            'href' => $routeUrl('site.explorar'),
            'match' => ['site.explorar*'],
        ],
        [
            'label' => 'Agenda',
            'href' => $routeUrl('site.agenda'),
            'match' => ['site.agenda', 'eventos.*'],
        ],
        [
            'label' => 'Mapa',
            'href' => $routeUrl('site.mapa'),
            'match' => ['site.mapa*'],
        ],
        [
            'label' => $perfilLabel,
            'href' => $perfilHref,
            'match' => ['site.perfil*', 'profile*', 'dashboard', 'admin.*', 'coordenador.*', 'login'],
        ],
    ])->filter(fn ($item) => filled($item['href']) && $item['href'] !== '#')->values();

    $navLabel = json_decode('"Se\u00e7\u00f5es do portal"');
@endphp

<header class="site-topbar">
    <div class="site-topbar-inner">
        <a href="{{ $routeUrl('site.home', url('/')) }}" class="site-brand">
            <img src="{{ theme_asset('logo') }}" alt="VisitAltamira" class="site-brand-logo" loading="lazy" decoding="async">
        </a>

        <nav class="site-topbar-nav" aria-label="{{ $navLabel }}">
            @foreach($sections as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ $item['href'] }}"
                   class="{{ $active ? 'site-chip site-chip-active' : 'site-chip' }}"
                   @if($active) aria-current="page" @endif>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>

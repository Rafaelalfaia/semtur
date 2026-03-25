@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route as R;

    $routeUrl = function (string $name, string $fallback = '#') {
        return R::has($name) ? route($name) : $fallback;
    };

    $homeHref = $routeUrl('site.home', url('/'));
    $perfilHref = R::has('login') ? route('login') : url('/login');
    $perfilLabel = 'Entrar';

    $u = Auth::user();
    if ($u) {
        $perfilLabel = 'Perfil';

        if (method_exists($u, 'hasRole')) {
            if ($u->hasRole('Admin') && R::has('admin.dashboard')) {
                $perfilHref = route('admin.dashboard');
                $perfilLabel = 'Painel';
            } elseif ($u->hasRole('Coordenador') && R::has('coordenador.dashboard')) {
                $perfilHref = route('coordenador.dashboard');
                $perfilLabel = 'Painel';
            } elseif ($u->hasRole('Cidadao') && R::has('site.perfil.index')) {
                $perfilHref = route('site.perfil.index');
            } elseif (R::has('dashboard')) {
                $perfilHref = route('dashboard');
            }
        }
    }

    $sections = collect([
        [
            'label' => 'Início',
            'href' => $routeUrl('site.home', url('/')),
            'match' => ['site.home'],
        ],
        [
            'label' => 'Descubra',
            'href' => R::has('site.explorar')
                ? route('site.explorar')
                : $routeUrl('site.descubra'),
            'match' => ['site.descubra', 'site.explorar*'],
        ],
        [
            'label' => 'Eventos',
            'href' => $routeUrl('site.agenda'),
            'match' => ['site.agenda', 'eventos.*'],
        ],
        [
            'label' => 'Mapa',
            'href' => $routeUrl('site.mapa'),
            'match' => ['site.mapa*'],
        ],
    ])->filter(fn ($item) => filled($item['href']) && $item['href'] !== '#')->values();
@endphp

<header class="site-topbar">
    <div class="site-topbar-inner">
        <a href="{{ $homeHref }}" class="site-brand">
            <img src="{{ theme_asset('logo') }}" alt="VisitAltamira" class="site-brand-logo" loading="lazy" decoding="async">
            <span class="site-brand-copy">
                <strong>VisitAltamira</strong>
                <span>Guia oficial</span>
            </span>
        </a>

        <div class="site-topbar-actions">
            <a href="{{ $perfilHref }}" class="site-button-secondary">{{ $perfilLabel }}</a>
        </div>
    </div>

    <nav class="site-topbar-nav" aria-label="Seções do portal">
        @foreach($sections as $item)
            @php $active = request()->routeIs($item['match']); @endphp
            <a href="{{ $item['href'] }}"
               class="{{ $active ? 'site-chip site-chip-active' : 'site-chip' }}"
               @if($active) aria-current="page" @endif>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</header>

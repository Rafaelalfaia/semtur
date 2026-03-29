@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route as R;

    $routeUrl = function (string $name, string $fallback = '#') {
        return R::has($name) ? localized_route($name) : $fallback;
    };

    $perfilHref = R::has('login') ? localized_route('login') : localized_route('site.home');
    $perfilLabel = __('ui.nav.login');
    $logoSources = site_image_sources(theme_asset('logo'), 'logo');
    $activeLocale = $currentLocale ?? request()->route('locale') ?? config('app.locale_prefix_fallback', 'pt');
    $route = request()->route();
    $routeName = $route?->getName();
    $routeParams = collect($route?->parameters() ?? [])->except('locale')->all();
    $iconMap = [
        'pt' => asset('icons/pt.png'),
        'en' => asset('icons/us.webp'),
        'es' => asset('icons/es.png'),
    ];
    $localeNameMap = [
        'pt' => __('ui.locale.pt'),
        'en' => __('ui.locale.en'),
        'es' => __('ui.locale.es'),
    ];
    $localeLinks = collect($supportedLocales ?? config('app.supported_locales', []))
        ->map(function ($meta, $prefix) use ($routeName, $routeParams, $activeLocale, $iconMap, $localeNameMap) {
            return [
                'prefix' => $prefix,
                'name' => $localeNameMap[$prefix] ?? data_get($meta, 'name', strtoupper($prefix)),
                'icon' => $iconMap[$prefix] ?? null,
                'href' => $routeName && R::has($routeName)
                    ? route($routeName, array_merge($routeParams, ['locale' => $prefix]))
                    : localized_route('site.home', ['locale' => $prefix]),
                'active' => $activeLocale === $prefix,
            ];
        })
        ->values();

    $u = Auth::user();
    if ($u) {
        $perfilLabel = __('ui.nav.profile');

        if (method_exists($u, 'hasRole')) {
            if ($u->hasRole('Admin') && R::has('admin.dashboard')) {
                $perfilHref = route('admin.dashboard');
            } elseif ($u->hasRole('Coordenador') && R::has('coordenador.dashboard')) {
                $perfilHref = route('coordenador.dashboard');
            } elseif ($u->hasRole('Cidadao') && R::has('site.perfil.index')) {
                $perfilHref = localized_route('site.perfil.index');
            } elseif (R::has('dashboard')) {
                $perfilHref = route('dashboard');
            }
        }
    }

    $sections = collect([
        ['label' => __('ui.nav.home'),'href' => $routeUrl('site.home', localized_route('site.home')),'match' => ['site.home']],
        ['label' => __('ui.nav.explore'),'href' => $routeUrl('site.explorar'),'match' => ['site.explorar*']],
        ['label' => __('ui.nav.agenda'),'href' => $routeUrl('site.agenda'),'match' => ['site.agenda', 'eventos.*']],
        ['label' => __('ui.nav.map'),'href' => $routeUrl('site.mapa'),'match' => ['site.mapa*']],
        ['label' => $perfilLabel,'href' => $perfilHref,'match' => ['site.perfil*', 'profile*', 'dashboard', 'admin.*', 'coordenador.*', 'login']],
    ])->filter(fn ($item) => filled($item['href']) && $item['href'] !== '#')->values();

    $navLabel = __('ui.nav.sections');
@endphp

<header class="site-topbar">
    <div class="site-topbar-inner">
        <a href="{{ $routeUrl('site.home', localized_route('site.home')) }}" class="site-brand">
            <x-picture
                :jpg="$logoSources['jpg'] ?? theme_asset('logo')"
                :webp="$logoSources['webp'] ?? null"
                alt="VisitAltamira"
                class="site-brand-logo"
                sizes="180px"
                :width="$logoSources['width'] ?? null"
                :height="$logoSources['height'] ?? null"
                priority
            />
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

        @if($localeLinks->isNotEmpty())
            <div class="site-topbar-locale" aria-label="{{ __('ui.locale.label') }}">
                @foreach($localeLinks as $localeItem)
                    <a href="{{ $localeItem['href'] }}"
                       class="{{ $localeItem['active'] ? 'site-locale-chip is-active' : 'site-locale-chip' }}"
                       hreflang="{{ strtolower($localeItem['prefix']) }}"
                       lang="{{ strtolower($localeItem['prefix']) }}"
                       title="{{ __('ui.locale.switch_to', ['language' => $localeItem['name']]) }}"
                       aria-label="{{ __('ui.locale.switch_to', ['language' => $localeItem['name']]) }}">
                        <img src="{{ $localeItem['icon'] }}" alt="" class="site-locale-icon" loading="lazy" decoding="async">
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</header>

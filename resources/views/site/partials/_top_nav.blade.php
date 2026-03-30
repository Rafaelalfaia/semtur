@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route as R;

    $routeUrl = function (string $name, string $fallback = '#') {
        return R::has($name) ? localized_route($name) : $fallback;
    };

    $perfilHref = R::has('login') ? localized_route('login') : localized_route('site.home');
    $perfilLabel = ui_text('ui.nav.login');
    $logoSources = site_image_sources(theme_asset('logo'), 'logo');
    $activeLocale = $currentLocale ?? request()->route('locale') ?? config('app.locale_prefix_fallback', 'pt');
    $route = request()->route();
    $routeName = $route?->getName();
    $routeParams = collect($route?->parameters() ?? [])->except('locale')->all();
    $localeNameMap = [
        'pt' => ui_text('ui.locale.pt'),
        'en' => ui_text('ui.locale.en'),
        'es' => ui_text('ui.locale.es'),
    ];
    $localeLinks = collect($supportedLocales ?? supported_locales())
        ->map(function ($meta, $prefix) use ($routeName, $routeParams, $activeLocale, $localeNameMap) {
            return [
                'prefix' => $prefix,
                'name' => $localeNameMap[$prefix] ?? data_get($meta, 'name', strtoupper($prefix)),
                'icon' => data_get($meta, 'icon'),
                'href' => $routeName && R::has($routeName)
                    ? route($routeName, array_merge($routeParams, ['locale' => $prefix]))
                    : localized_route('site.home', ['locale' => $prefix]),
                'active' => $activeLocale === $prefix,
                'html_lang' => data_get($meta, 'html_lang', strtolower($prefix)),
                'hreflang' => data_get($meta, 'hreflang', strtolower($prefix)),
            ];
        })
        ->values();

    $u = Auth::user();
    if ($u) {
        $perfilLabel = ui_text('ui.nav.profile');

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
        ['label' => ui_text('ui.nav.home'),'href' => $routeUrl('site.home', localized_route('site.home')),'match' => ['site.home']],
        ['label' => ui_text('ui.nav.explore'),'href' => $routeUrl('site.explorar'),'match' => ['site.explorar*']],
        ['label' => ui_text('ui.nav.agenda'),'href' => $routeUrl('site.agenda'),'match' => ['site.agenda', 'eventos.*']],
        ['label' => ui_text('ui.nav.map'),'href' => $routeUrl('site.mapa'),'match' => ['site.mapa*']],
        ['label' => $perfilLabel,'href' => $perfilHref,'match' => ['site.perfil*', 'profile*', 'dashboard', 'admin.*', 'coordenador.*', 'login']],
    ])->filter(fn ($item) => filled($item['href']) && $item['href'] !== '#')->values();

    $navLabel = ui_text('ui.nav.sections');
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
            <div class="site-topbar-locale" aria-label="{{ ui_text('ui.locale.label') }}">
                @foreach($localeLinks as $localeItem)
                    <a href="{{ $localeItem['href'] }}"
                       class="{{ $localeItem['active'] ? 'site-locale-chip is-active' : 'site-locale-chip' }}"
                       hreflang="{{ $localeItem['hreflang'] }}"
                       lang="{{ $localeItem['html_lang'] }}"
                       title="{{ ui_text('ui.locale.switch_to', ['language' => $localeItem['name']]) }}"
                       aria-label="{{ ui_text('ui.locale.switch_to', ['language' => $localeItem['name']]) }}">
                        @if($localeItem['icon'])
                            <img src="{{ $localeItem['icon'] }}" alt="" class="site-locale-icon" loading="lazy" decoding="async">
                        @else
                            <span class="site-locale-icon flex items-center justify-center bg-white/70 text-[10px] font-semibold text-slate-700">{{ $localeItem['prefix'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</header>

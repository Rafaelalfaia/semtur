@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'canonical' => null,
    'type' => 'website',
    'locale' => null,
    'noindex' => false,
])

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $siteName = config('app.name', 'VisitAltamira');
    $baseUrl = rtrim(config('app.url') ?: url('/'), '/');
    $fallbackImage = theme_asset('hero_image');
    $activePrefix = request()->route('locale') ?? config('app.locale_prefix_fallback', 'pt');
    $supportedLocales = supported_locales();
    $activeLocaleMeta = $supportedLocales[$activePrefix] ?? locale_meta($activePrefix);
    $resolvedLocale = $locale ?: data_get($activeLocaleMeta, 'og_locale', 'pt_BR');
    $htmlLocale = data_get($activeLocaleMeta, 'html_lang', $activePrefix === 'pt' ? 'pt-BR' : $activePrefix);

    $fallbackTitle = 'VisitAltamira - Altamira, Rio Xingu (Para, Amazonia)';
    $fallbackDesc = 'Guia oficial de Altamira e do Rio Xingu no Pará: pontos turísticos, experiências, gastronomia e serviços para planejar a visita com mais contexto.';

    $resolvedTitle = trim((string) $title);
    $titleTag = $resolvedTitle === ''
        ? $fallbackTitle
        : (Str::contains(Str::lower($resolvedTitle), Str::lower($siteName)) ? $resolvedTitle : $resolvedTitle.' - '.$siteName);

    $desc = trim((string) ($description ?: $fallbackDesc)) ?: $fallbackDesc;

    $toAbsoluteUrl = function ($value) use ($baseUrl) {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }
        return $baseUrl.'/'.ltrim($value, '/');
    };

    $imgUrl = $toAbsoluteUrl($image) ?: $toAbsoluteUrl($fallbackImage);
    $canon = $toAbsoluteUrl($canonical) ?: url()->current();
    $v = app()->environment('local') ? ('?v='.now()->timestamp) : '';

    $route = request()->route();
    $routeName = $route?->getName();
    $routeParams = collect($route?->parameters() ?? [])->except('locale')->all();
    $alternateLinks = collect($supportedLocales)->mapWithKeys(function ($meta, $prefix) use ($routeName, $routeParams, $canon) {
        $href = $routeName && Route::has($routeName)
            ? route($routeName, array_merge($routeParams, ['locale' => $prefix]))
            : $canon;
        return [data_get($meta, 'hreflang', $prefix) => $href];
    });

    $hrefPtBr = $alternateLinks->get('pt-BR', $canon);
    $hrefXDef = $hrefPtBr ?: $canon;
    $keywords = 'VisitAltamira, Altamira, Pará, Amazônia, Rio Xingu, turismo, guia turístico, gastronomia, hospedagem, experiências';
    $maskIconExists = file_exists(public_path('icons/mask-icon.svg'));
    $logoUrl = $toAbsoluteUrl('icons/pwa-512.png') ?: $imgUrl;
    $searchTarget = Route::has('site.explorar') ? localized_route('site.explorar').'?q={search_term_string}' : null;

    $websiteId = $baseUrl.'#website';
    $organizationId = $baseUrl.'#organization';
    $graph = [
        [
            '@type' => 'Organization',
            '@id' => $organizationId,
            'name' => 'VisitAltamira',
            'alternateName' => 'Visit Altamira',
            'url' => $baseUrl,
            'logo' => $logoUrl,
            'areaServed' => ['@type' => 'AdministrativeArea', 'name' => 'Altamira, Para, Brasil'],
            'sameAs' => ['https://www.instagram.com/visitaltamira/', 'https://www.facebook.com/visitaltamira'],
        ],
        array_filter([
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'name' => 'VisitAltamira',
            'url' => $baseUrl,
            'inLanguage' => $htmlLocale,
            'publisher' => ['@id' => $organizationId],
            'potentialAction' => $searchTarget ? ['@type' => 'SearchAction', 'target' => $searchTarget, 'query-input' => 'required name=search_term_string'] : null,
        ], fn ($value) => $value !== null),
        [
            '@type' => 'WebPage',
            '@id' => $canon.'#webpage',
            'url' => $canon,
            'name' => $titleTag,
            'description' => Str::limit(strip_tags($desc), 200),
            'inLanguage' => $htmlLocale,
            'isPartOf' => ['@id' => $websiteId],
            'about' => [
                ['@type' => 'Place', 'name' => 'Amazonia', 'sameAs' => 'https://pt.wikipedia.org/wiki/Amaz%C3%B4nia'],
                ['@type' => 'RiverBodyOfWater', 'name' => 'Rio Xingu', 'sameAs' => 'https://pt.wikipedia.org/wiki/Rio_Xingu'],
                ['@type' => 'City', 'name' => 'Altamira', 'sameAs' => 'https://pt.wikipedia.org/wiki/Altamira_(Par%C3%A1)'],
                ['@type' => 'AdministrativeArea', 'name' => 'Para', 'sameAs' => 'https://pt.wikipedia.org/wiki/Par%C3%A1'],
            ],
            'primaryImageOfPage' => ['@type' => 'ImageObject', 'url' => $imgUrl],
        ],
    ];
@endphp

<title>{{ $titleTag }}</title>
<meta name="description" content="{{ Str::limit(strip_tags($desc), 160) }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="robots" content="{{ $noindex ? 'noindex,nofollow' : 'index,follow,max-image-preview:large' }}">
<meta name="google" content="notranslate">
<meta http-equiv="content-language" content="{{ $htmlLocale }}">

<link rel="canonical" href="{{ $canon }}">

@foreach($alternateLinks as $hreflang => $href)
<link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $hrefXDef }}">

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $titleTag }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($desc), 200) }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $canon }}">
<meta property="og:image" content="{{ $imgUrl }}">
<meta property="og:image:secure_url" content="{{ $imgUrl }}">
<meta property="og:image:alt" content="{{ $resolvedTitle !== '' ? $resolvedTitle : 'VisitAltamira em Altamira e no Rio Xingu' }}">
<meta property="og:locale" content="{{ $resolvedLocale }}">
@foreach(collect($supportedLocales)->pluck('og_locale')->filter()->unique()->reject(fn ($item) => $item === $resolvedLocale) as $altLocale)
<meta property="og:locale:alternate" content="{{ $altLocale }}">
@endforeach

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $titleTag }}">
<meta name="twitter:description" content="{{ Str::limit(strip_tags($desc), 200) }}">
<meta name="twitter:image" content="{{ $imgUrl }}">
<meta name="twitter:image:alt" content="{{ $resolvedTitle !== '' ? $resolvedTitle : 'VisitAltamira em Altamira e no Rio Xingu' }}">

<link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32.png{{ $v }}">
<link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16.png{{ $v }}">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png{{ $v }}">
@if ($maskIconExists)
<link rel="mask-icon" href="/icons/mask-icon.svg{{ $v }}" color="#0e1b12">
@endif
<link rel="manifest" href="{{ Route::has('site.manifest') ? route('site.manifest', ['locale' => $activePrefix]).$v : '/manifest.webmanifest'.$v }}">
<link rel="shortcut icon" href="/favicon.ico{{ $v }}">
<meta name="theme-color" content="#0e1b12">

<link rel="preload" as="image" href="{{ $imgUrl }}">

<script type="application/ld+json">@json(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>

{{ $slot }}





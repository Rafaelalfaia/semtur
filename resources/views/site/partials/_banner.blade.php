@include('site.partials._hero_destaque', [
    'banner' => $banner ?? null,
    'title' => $title ?? null,
    'subtitle' => $subtitle ?? null,
    'ctaLabel' => $ctaLabel ?? null,
    'href' => $href ?? null,
    'secondaryCtaLabel' => $secondaryCtaLabel ?? null,
    'secondaryHref' => $secondaryHref ?? null,
    'overlayImage' => $overlayImage ?? null,
    'overlayImageAlt' => $overlayImageAlt ?? null,
    'overlayOnly' => $overlayOnly ?? false,
    'heroClass' => $heroClass ?? null,
])

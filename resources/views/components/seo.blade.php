@props([
  'title' => null,
  'description' => null,
  'image' => null,        // caminho relativo ou URL absoluta
  'canonical' => null,
  'type' => 'website',    // 'article' nas páginas de conteúdo
  'locale' => 'pt_BR',
  'noindex' => false,     // opcional
])

@php
  $siteName = config('app.name', 'VisitAltamira');
  $baseUrl  = rtrim(config('app.url'), '/');

  // ---------- Fallbacks pensados para “Amazônia / Xingu / Altamira / Pará”
  $fallbackTitle = 'VisitAltamira — Altamira, Rio Xingu (Pará, Amazônia)';
  $fallbackDesc  = 'Guia oficial de Altamira e do Rio Xingu no Pará (Amazônia): pontos turísticos, experiências, gastronomia e empresas locais. Descubra o Xingu.';

  $titleTag = $title ? ($title.' — '.$siteName) : $fallbackTitle;
  $desc     = $description ?: $fallbackDesc;

  $imgUrl   = $image
            ? (Str::startsWith($image, ['http://','https://','/']) ? $image : $baseUrl.'/'.ltrim($image,'/'))
            : $baseUrl.'/images/og-default.jpg';

  // Canonical consistente
  $canon    = $canonical ?: url()->current();

  // ✅ querystring só no ambiente local para quebrar cache de favicons
  $v = app()->environment('local') ? ('?v='.now()->timestamp) : '';

  // Hreflang: se não tiver versões, mantemos pt-BR e x-default apontando para o canonical
  $hrefPtBr = $canon;
  $hrefXDef = $canon;

  // Palavras-chave (não influenciam muito, mas não atrapalham)
  $keywords = 'VisitAltamira, Altamira, Pará, Amazônia, Rio Xingu, Xingu, turismo, guia, pontos turísticos, gastronomia, hotéis';
@endphp

<title>{{ $titleTag }}</title>
<meta name="description" content="{{ Str::limit(strip_tags($desc), 160) }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="robots" content="{{ $noindex ? 'noindex,nofollow' : 'index,follow,max-image-preview:large' }}">

<link rel="canonical" href="{{ $canon }}">

{{-- Hreflang --}}
<link rel="alternate" hreflang="pt-BR" href="{{ $hrefPtBr }}">
<link rel="alternate" hreflang="x-default" href="{{ $hrefXDef }}">

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $titleTag }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($desc), 200) }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $canon }}">
<meta property="og:image" content="{{ $imgUrl }}">
<meta property="og:image:alt" content="VisitAltamira — Altamira e Rio Xingu (Pará, Amazônia)">
<meta property="og:locale" content="pt_BR">
<meta property="og:locale:alternate" content="en_US">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $titleTag }}">
<meta name="twitter:description" content="{{ Str::limit(strip_tags($desc), 200) }}">
<meta name="twitter:image" content="{{ $imgUrl }}">

{{-- Favicons/manifest --}}
<link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32.png{{ $v }}">
<link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16.png{{ $v }}">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png{{ $v }}">
<link rel="mask-icon" href="/icons/mask-icon.svg" color="#0e1b12">
<link rel="manifest" href="/manifest.webmanifest{{ $v }}">
<link rel="shortcut icon" href="/favicon.ico{{ $v }}">
<meta name="theme-color" content="#0e1b12">

{{-- Performance/LCP --}}
<link rel="preload" as="image" href="{{ $imgUrl }}">

{{-- JSON-LD: Organization + WebSite + WebPage (com "about" nas entidades Amazônia/Xingu/Altamira/Pará) --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "name": "VisitAltamira",
      "url": "{{ $baseUrl }}",
      "logo": "{{ $baseUrl }}/icons/pwa-512.png",
      "areaServed": {
        "@type": "AdministrativeArea",
        "name": "Altamira, Pará, Brasil"
      },
      "sameAs": [
        "https://www.instagram.com/visitaltamira/",
        "https://www.facebook.com/visitaltamira"
      ]
    },
    {
      "@type": "WebSite",
      "name": "VisitAltamira",
      "url": "{{ $baseUrl }}",
      "inLanguage": "pt-BR",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ $baseUrl }}/buscar?q={query}",
        "query-input": "required name=query"
      }
    },
    {
      "@type": "WebPage",
      "url": "{{ $canon }}",
      "name": "{{ $titleTag }}",
      "description": "{{ Str::limit(strip_tags($desc), 200) }}",
      "inLanguage": "pt-BR",
      "isPartOf": { "@id": "{{ $baseUrl }}#" },
      "about": [
        {
          "@type": "Place",
          "name": "Amazônia",
          "sameAs": "https://pt.wikipedia.org/wiki/Amaz%C3%B4nia"
        },
        {
          "@type": "RiverBodyOfWater",
          "name": "Rio Xingu",
          "sameAs": "https://pt.wikipedia.org/wiki/Rio_Xingu"
        },
        {
          "@type": "City",
          "name": "Altamira",
          "sameAs": "https://pt.wikipedia.org/wiki/Altamira_(Par%C3%A1)"
        },
        {
          "@type": "AdministrativeArea",
          "name": "Pará",
          "sameAs": "https://pt.wikipedia.org/wiki/Par%C3%A1"
        }
      ],
      "primaryImageOfPage": {
        "@type": "ImageObject",
        "url": "{{ $imgUrl }}"
      }
    }
  ]
}
</script>

{{ $slot }}

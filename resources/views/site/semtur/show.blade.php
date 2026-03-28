@extends('site.layouts.app')

@php
    $semturCanonical = route('site.semtur');
    $semturTitle = ($sec->nome ?? 'SEMTUR').' de Altamira';
    $semturDescription = \Illuminate\Support\Str::limit(strip_tags($sec->descricao ?? 'Informações institucionais da SEMTUR de Altamira.'), 160);
    $semturImage = $sec->foto_capa_url ?: $sec->foto_url ?: theme_asset('hero_image');
    $semturRedes = is_array($sec->redes ?? null) ? array_filter($sec->redes) : [];
    $semturSchema = [
        [
            '@type' => 'BreadcrumbList',
            '@id' => $semturCanonical.'#breadcrumbs',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Início',
                    'item' => \Illuminate\Support\Facades\Route::has('site.home') ? route('site.home') : url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Secretaria',
                    'item' => $semturCanonical,
                ],
            ],
        ],
        array_filter([
            '@type' => 'GovernmentOrganization',
            '@id' => $semturCanonical.'#organization',
            'name' => $sec->nome ?? 'SEMTUR',
            'url' => $semturCanonical,
            'description' => $semturDescription,
            'image' => [$semturImage],
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Altamira',
                'addressRegion' => 'PA',
                'addressCountry' => 'BR',
            ],
            'sameAs' => array_values($semturRedes) ?: null,
        ], fn ($value) => $value !== null),
    ];
@endphp

@section('title', $semturTitle)
@section('meta.description', $semturDescription)
@section('meta.image', $semturImage)
@section('meta.canonical', $semturCanonical)
@section('meta.type', 'website')

@push('structured-data')
<script type="application/ld+json">@json(['@context' => 'https://schema.org', '@graph' => $semturSchema], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
@endpush

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;

    $heroUrl = $sec->foto_capa_url ?: ($sec->foto_url ?: theme_asset('hero_image'));
    $logoUrl = $sec->foto_url ?: theme_asset('logo');
    $redes = is_array($sec->redes ?? null) ? $sec->redes : [];
    $redesLabels = [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'linkedin' => 'LinkedIn',
        'site' => 'Site',
        'whatsapp' => 'WhatsApp',
    ];
    $redeItems = collect($redesLabels)
        ->map(fn ($label, $key) => ['label' => $label, 'href' => $redes[$key] ?? null])
        ->filter(fn ($item) => filled($item['href']))
        ->values();

    $membroCards = collect($membros ?? [])->map(fn ($membro) => [
        'title' => $membro->nome,
        'subtitle' => $membro->cargo ?: 'Equipe SEMTUR',
        'summary' => \Illuminate\Support\Str::limit(strip_tags((string) $membro->resumo), 96),
        'image' => $membro->foto_url ?: asset('imagens/avatar.png'),
        'href' => null,
        'badge' => 'Equipe',
    ]);
@endphp

<div class="site-page site-page-shell site-semtur-page">
    @include('site.partials._page_hero', [
        'backHref' => Route::has('site.home') ? route('site.home') : url('/'),
        'breadcrumbs' => [
            ['label' => 'Início', 'href' => Route::has('site.home') ? route('site.home') : url('/')],
            ['label' => 'Secretaria'],
        ],
        'badge' => 'Secretaria',
        'title' => $sec->nome ?? 'SEMTUR',
        'subtitle' => 'Informações institucionais, equipe e canais públicos da Secretaria Municipal de Turismo de Altamira.',
        'meta' => [],
        'primaryActionLabel' => Route::has('site.mapa') ? 'Ver mapa turístico' : null,
        'primaryActionHref' => Route::has('site.mapa') ? route('site.mapa') : null,
        'secondaryActionLabel' => Route::has('site.contato') ? 'Contato' : null,
        'secondaryActionHref' => Route::has('site.contato') ? route('site.contato') : null,
        'image' => $heroUrl,
        'imageAlt' => $sec->nome ?? 'SEMTUR',
        'compact' => true,
    ])

    <section class="site-section">
        <div class="site-editorial-layout">
            <div class="site-editorial-main">
                <section class="site-surface site-content-block">
                    <div class="site-detail-profile">
                        <img src="{{ site_image_url($logoUrl, "avatar") }}" alt="{{ $sec->nome ?? 'SEMTUR' }}" class="site-detail-avatar" loading="lazy" decoding="async">
                        <div>
                            <x-section-head eyebrow="Sobre" title="Secretaria Municipal de Turismo" subtitle="Apresentação pública da secretaria e do papel institucional no ecossistema turístico da cidade." />
                        </div>
                    </div>

                    <div class="site-prose">
                        {!! nl2br(e($sec->descricao ?: 'Informações institucionais da Secretaria Municipal de Turismo de Altamira.')) !!}
                    </div>
                </section>
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    <x-section-head eyebrow="Redes" title="Canais públicos" />

                    @if($redeItems->isEmpty())
                        <div class="site-empty-state">
                            <p class="site-empty-state-copy">Ainda não há canais públicos informados nesta página.</p>
                        </div>
                    @else
                        <div class="site-detail-links">
                            @foreach($redeItems as $item)
                                <a href="{{ $item['href'] }}" target="_blank" rel="noopener noreferrer" class="site-link">{{ $item['label'] }}</a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </aside>
        </div>
    </section>

    <section class="site-section">
        <x-section-head
            eyebrow="Equipe"
            title="Equipe SEMTUR"
            subtitle="Profissionais publicados que ajudam a compor a presença institucional do portal."
        />

        @if($membroCards->isEmpty())
            <div class="site-empty-state">
                <p class="site-empty-state-copy">A equipe pública será exibida aqui assim que houver membros publicados.</p>
            </div>
        @else
            <div class="site-card-list-grid">
                @foreach($membroCards as $item)
                    <div class="site-card-list">
                        <div class="site-card-list-media">
                            <img src="{{ site_image_url($item['image'], "card") }}" alt="{{ $item['title'] }}" class="site-card-list-image" loading="lazy" decoding="async">
                        </div>
                        <div class="site-card-list-body">
                            <span class="site-badge">{{ $item['badge'] }}</span>
                            <h3 class="site-card-list-title">{{ $item['title'] }}</h3>
                            <div class="site-card-list-meta"><span>{{ $item['subtitle'] }}</span></div>
                            @if($item['summary'])
                                <p class="site-card-list-summary">{{ $item['summary'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection

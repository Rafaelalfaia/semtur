@props([
    'backHref' => null,
    'breadcrumbs' => [],
    'badge' => null,
    'title' => null,
    'subtitle' => null,
    'meta' => [],
    'primaryActionLabel' => null,
    'primaryActionHref' => null,
    'secondaryActionLabel' => null,
    'secondaryActionHref' => null,
    'image' => null,
    'imageAlt' => null,
    'compact' => false,
    'textEditor' => null,
    'imageEditor' => null,
])

@php
    $heroImage = $image ?: theme_asset('hero_image');
    $heroSources = site_image_sources($heroImage, 'hero');
    $resolvedBackHref = $backHref ?: url()->previous();
    $meta = collect($meta)->filter(fn ($item) => filled($item))->values();
    $heroClasses = trim('site-detail-hero site-page-hero'.($compact ? ' site-page-hero-compact' : ''));
@endphp

<section class="site-section">
    <div class="{{ $heroClasses }}">
        <x-picture
            :jpg="$heroSources['jpg'] ?? $heroImage"
            :webp="$heroSources['webp'] ?? null"
            :alt="$imageAlt ?: $title"
            class="site-detail-hero-image"
            sizes="100vw"
            :width="$heroSources['width'] ?? null"
            :height="$heroSources['height'] ?? null"
            priority
        />

        <div class="site-detail-hero-overlay site-page-hero-overlay">
            <div class="site-page-hero-top">
                <a href="{{ $resolvedBackHref }}" class="site-page-back">
                    <svg class="site-page-back-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>{{ ui_text('ui.common.back') }}</span>
                </a>

                @if(!empty($breadcrumbs))
                    <nav class="site-page-breadcrumbs" aria-label="Breadcrumb">
                        @foreach($breadcrumbs as $item)
                            @if(!empty($item['href']))
                                <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                            @else
                                <span class="site-page-breadcrumbs-current">{{ $item['label'] }}</span>
                            @endif
                        @endforeach
                    </nav>
                @endif

                @if(!empty($imageEditor))
                    <div class="site-page-hero-editor site-page-hero-editor--image">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $imageEditor['title'] ?? $title,
                            'editorPage' => $imageEditor['page'] ?? 'site.page',
                            'editorKey' => $imageEditor['key'] ?? 'hero',
                            'editorLabel' => $imageEditor['label'] ?? 'Imagem da seção',
                            'editorLocale' => $imageEditor['locale'] ?? route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => $imageEditor['trigger_label'] ?? 'Editar imagem',
                            'editorFields' => ['media'],
                            'editableTranslation' => $imageEditor['translation'] ?? null,
                            'editableMedia' => $imageEditor['media'] ?? null,
                            'editableStatus' => $imageEditor['status'] ?? 'publicado',
                            'editorMediaSlot' => $imageEditor['media_slot'] ?? 'hero',
                            'editorMediaLabel' => $imageEditor['media_label'] ?? 'Imagem',
                            'editorMediaPreviewLabel' => $imageEditor['preview_label'] ?? 'imagem atual',
                            'editableFallback' => [
                                'titulo' => $title,
                            ],
                        ])
                    </div>
                @endif
            </div>

            <div class="site-detail-hero-copy site-page-hero-copy">
                @if(!empty($textEditor))
                    <div class="site-page-hero-editor site-page-hero-editor--text">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $textEditor['title'] ?? $title,
                            'editorPage' => $textEditor['page'] ?? 'site.page',
                            'editorKey' => $textEditor['key'] ?? 'hero',
                            'editorLabel' => $textEditor['label'] ?? 'Texto da seção',
                            'editorLocale' => $textEditor['locale'] ?? route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => $textEditor['trigger_label'] ?? 'Editar texto',
                            'editorFields' => $textEditor['fields'] ?? ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href'],
                            'editableTranslation' => $textEditor['translation'] ?? null,
                            'editableStatus' => $textEditor['status'] ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => $badge,
                                'titulo' => $title,
                                'lead' => $subtitle,
                                'cta_label' => $primaryActionLabel,
                                'cta_href' => $primaryActionHref,
                            ],
                        ])
                    </div>
                @endif

                @if($badge)
                    <span class="site-badge">{{ $badge }}</span>
                @endif

                <h1 class="site-detail-title site-page-hero-title">{{ $title }}</h1>

                @if($subtitle)
                    <p class="site-detail-subtitle site-page-hero-subtitle">{{ $subtitle }}</p>
                @endif

                @if($meta->isNotEmpty())
                    <div class="site-page-hero-meta">
                        @foreach($meta as $item)
                            <span class="site-page-hero-meta-item">{{ $item }}</span>
                        @endforeach
                    </div>
                @endif

                @if(($primaryActionLabel && $primaryActionHref) || ($secondaryActionLabel && $secondaryActionHref))
                    <div class="site-page-hero-actions">
                        @if($primaryActionLabel && $primaryActionHref)
                            <a href="{{ $primaryActionHref }}" class="site-button-primary">{{ $primaryActionLabel }}</a>
                        @endif

                        @if($secondaryActionLabel && $secondaryActionHref)
                            <a href="{{ $secondaryActionHref }}" class="site-button-secondary">{{ $secondaryActionLabel }}</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

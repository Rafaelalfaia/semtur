@extends('site.layouts.app')

@section('title', $espaco->nome . ' • Museu')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) ($espaco->resumo ?: $espaco->descricao)), 160))
@section('meta.image', $espaco->capa_url ?: (optional($espaco->midias->first())->url ?: asset('imagens/altamira.jpg')))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;

    $pageBlocks = $pageBlocks ?? collect();
    $museuShowBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'about_section' => $pageBlocks->get('about_section'),
        'gallery_section' => $pageBlocks->get('gallery_section'),
        'info_section' => $pageBlocks->get('info_section'),
        'schedule_section' => $pageBlocks->get('schedule_section'),
        'booking_section' => $pageBlocks->get('booking_section'),
        'empty_state' => $pageBlocks->get('empty_state'),
    ];
    $museuShowTranslation = fn (string $key) => $museuShowBlocks[$key]?->getAttribute('traducao_resolvida');
    $aboutTranslation = $museuShowTranslation('about_section');
    $galleryTranslation = $museuShowTranslation('gallery_section');
    $infoTranslation = $museuShowTranslation('info_section');
    $scheduleTranslation = $museuShowTranslation('schedule_section');
    $bookingTranslation = $museuShowTranslation('booking_section');
    $emptyTranslation = $museuShowTranslation('empty_state');

    $fallback = asset('imagens/altamira.jpg');
    $capa = $heroMedia?->url ?: ($espaco->capa_url ?: optional($espaco->midias->first())->url ?: $fallback);
    $galeria = collect($espaco->midias ?? [])->map(fn ($midia) => [
        'src' => $midia->url,
        'alt' => $midia->alt ?: $espaco->nome,
    ])->values();

    $localizacao = collect([
        ['label' => 'Tipo', 'value' => 'Museu'],
        ['label' => 'Endereço', 'value' => $espaco->endereco],
        ['label' => 'Bairro', 'value' => $espaco->bairro],
        ['label' => 'Cidade', 'value' => $espaco->cidade ?: 'Altamira'],
    ])->filter(fn ($item) => filled($item['value']))->values();

    $agendamentoWhatsappHref = null;
    if ($espaco->agendamento_disponivel && filled($espaco->agendamento_whatsapp_phone)) {
        $mensagemAgendamento = implode("\n", array_filter([
            'Olá! Gostaria de fazer um agendamento para visitar '.$espaco->nome.'.',
            'Tipo: Museu',
            filled($espaco->cidade) ? 'Cidade: '.$espaco->cidade : 'Cidade: Altamira',
            'Pode me orientar sobre disponibilidade e próximos passos?',
        ]));

        $agendamentoWhatsappHref = 'https://wa.me/'.$espaco->agendamento_whatsapp_phone.'?text='.rawurlencode($mensagemAgendamento);
    }

    $heroBadge = $heroTranslation?->eyebrow ?: 'Museu';
    $heroTitle = $heroTranslation?->titulo ?: $espaco->nome;
    $heroSubtitle = $heroTranslation?->lead ?: 'Informações públicas, horários e orientações para planejar sua visita ao museu.';
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ($agendamentoWhatsappHref ? 'Fazer agendamento' : ($espaco->agendamento_disponivel ? 'Agendar visita' : null));
    $heroPrimaryHref = $heroTranslation?->cta_href ?: ($agendamentoWhatsappHref ?: ($espaco->agendamento_disponivel ? localized_route('site.museus.agendar', ['espaco' => $espaco->slug]) : null));

    $aboutEyebrow = $aboutTranslation?->eyebrow ?: 'Sobre';
    $aboutTitle = $aboutTranslation?->titulo ?: 'Planeje a visita';
    $aboutSubtitle = $aboutTranslation?->lead ?: 'Leia a apresentação pública do museu com contexto claro e orientação direta para a visita.';
    $galleryEyebrow = $galleryTranslation?->eyebrow ?: 'Galeria';
    $galleryTitle = $galleryTranslation?->titulo ?: 'Imagens do museu';
    $gallerySubtitle = $galleryTranslation?->lead ?: 'Abra as fotos para ver melhor o ambiente e navegar pelas imagens publicadas.';
    $infoEyebrow = $infoTranslation?->eyebrow ?: 'Informações';
    $infoTitle = $infoTranslation?->titulo ?: 'Dados públicos';
    $infoSubtitle = $infoTranslation?->lead ?: 'Tudo o que ajuda a decidir se este museu é adequado para sua visita.';
    $scheduleEyebrow = $scheduleTranslation?->eyebrow ?: 'Grade semanal';
    $scheduleTitle = $scheduleTranslation?->titulo ?: 'Horários disponíveis';
    $scheduleSubtitle = $scheduleTranslation?->lead ?: 'Consulte dias, faixas de visita e observações publicadas pelo coordenador.';
    $bookingEyebrow = $bookingTranslation?->eyebrow ?: 'Agendamento';
    $bookingTitle = $bookingTranslation?->titulo ?: 'Solicite sua visita';
    $bookingSubtitle = $bookingTranslation?->lead ?: 'Use o formulário público ou fale direto no WhatsApp do atendimento cadastrado pelo coordenador.';
    $emptyTitle = $emptyTranslation?->titulo ?: 'Sem imagens publicadas';
    $emptyCopy = $emptyTranslation?->lead ?: 'Este museu ainda não possui galeria pública disponível.';

    $canManageMuseu = auth()->check() && auth()->user()->can('espacos_culturais.update');
    $editMuseuHref = $canManageMuseu && Route::has('coordenador.espacos-culturais.edit')
        ? route('coordenador.espacos-culturais.edit', $espaco)
        : null;
@endphp

<div class="site-page site-page-shell site-espacos-page">
    @include('site.partials._page_hero', [
        'backHref' => Route::has('site.museus') ? localized_route('site.museus') : url()->previous(),
        'breadcrumbs' => [
            ['label' => 'Início', 'href' => localized_route('site.home')],
            ['label' => 'Museus', 'href' => Route::has('site.museus') ? localized_route('site.museus') : null],
            ['label' => $espaco->nome],
        ],
        'badge' => $heroBadge,
        'title' => $heroTitle,
        'subtitle' => $heroSubtitle,
        'meta' => [
            $espaco->bairro,
            $espaco->cidade ?: 'Altamira',
            $espaco->agendamento_disponivel ? 'Agendamento disponível' : 'Sem agendamento online',
        ],
        'primaryActionLabel' => $heroPrimaryLabel,
        'primaryActionHref' => $heroPrimaryHref,
        'secondaryActionLabel' => $espaco->maps_url ? ui_text('ui.common.see_on_map') : 'Voltar para museus',
        'secondaryActionHref' => $espaco->maps_url ?: localized_route('site.museus'),
        'image' => $capa,
        'imageAlt' => $heroTitle,
        'textEditor' => [
            'title' => $heroTitle,
            'page' => 'site.museus.show',
            'key' => 'hero',
            'label' => 'Texto da capa do museu',
            'locale' => route_locale(),
            'trigger_label' => 'Editar texto',
            'fields' => ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href'],
            'translation' => $heroTranslation ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
        ],
        'imageEditor' => [
            'title' => $heroTitle,
            'page' => 'site.museus.show',
            'key' => 'hero',
            'label' => 'Imagem da capa do museu',
            'locale' => route_locale(),
            'trigger_label' => 'Editar imagem',
            'translation' => $heroTranslation ?? null,
            'media' => $heroMedia ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
            'media_slot' => 'hero',
            'media_label' => 'Imagem da capa',
            'preview_label' => 'imagem atual da capa',
        ],
    ])

    <section class="site-section">
        <div class="site-editorial-layout site-espacos-layout">
            <div class="site-editorial-main">
                <section class="site-surface site-content-block">
                    @include('site.partials._content_editor', [
                        'editorTitle' => $aboutTitle,
                        'editorPage' => 'site.museus.show',
                        'editorKey' => 'about_section',
                        'editorLabel' => 'Seção sobre do museu',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline-compact',
                        'editorTriggerLabel' => 'Editar texto',
                        'editorFields' => ['eyebrow', 'titulo', 'lead'],
                        'editableTranslation' => $aboutTranslation,
                        'editableStatus' => $museuShowBlocks['about_section']?->status ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => 'Sobre',
                            'titulo' => 'Planeje a visita',
                            'lead' => 'Leia a apresentação pública do museu com contexto claro e orientação direta para a visita.',
                        ],
                    ])

                    @if($editMuseuHref)
                        <div class="site-inline-actions">
                            <a href="{{ $editMuseuHref }}" class="site-button-secondary">Editar dados do museu</a>
                        </div>
                    @endif

                    <div class="site-detail-profile site-espacos-detail-profile">
                        <img src="{{ site_image_url($capa, 'avatar') }}" alt="{{ $espaco->nome }}" class="site-detail-avatar" loading="lazy" decoding="async">
                        <div>
                            <x-section-head
                                :eyebrow="$aboutEyebrow"
                                :title="$aboutTitle"
                                :subtitle="$aboutSubtitle"
                            />
                        </div>
                    </div>

                    <div class="site-detail-copy site-prose">
                        {!! $espaco->descricao ? nl2br(e($espaco->descricao)) : '<p>Este museu ainda não tem uma apresentação pública detalhada.</p>' !!}
                    </div>
                </section>

                @if($galeria->isNotEmpty())
                    <section
                        class="site-surface-soft site-content-block"
                        x-data="{
                            open:false,
                            index:0,
                            images:@js($galeria),
                            show(i){ this.index=i; this.open=true; document.body.style.overflow='hidden'; },
                            close(){ this.open=false; document.body.style.overflow=''; },
                            next(){ this.index=(this.index+1)%this.images.length; },
                            prev(){ this.index=(this.index-1+this.images.length)%this.images.length; }
                        }"
                    >
                        @include('site.partials._content_editor', [
                            'editorTitle' => $galleryTitle,
                            'editorPage' => 'site.museus.show',
                            'editorKey' => 'gallery_section',
                            'editorLabel' => 'Seção galeria do museu',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['eyebrow', 'titulo', 'lead'],
                            'editableTranslation' => $galleryTranslation,
                            'editableStatus' => $museuShowBlocks['gallery_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => 'Galeria',
                                'titulo' => 'Imagens do museu',
                                'lead' => 'Abra as fotos para ver melhor o ambiente e navegar pelas imagens publicadas.',
                            ],
                        ])

                        @if($editMuseuHref)
                            <div class="site-inline-actions">
                                <a href="{{ $editMuseuHref }}" class="site-button-secondary">Editar imagens</a>
                            </div>
                        @endif

                        <x-section-head
                            :eyebrow="$galleryEyebrow"
                            :title="$galleryTitle"
                            :subtitle="$gallerySubtitle"
                        />

                        <div class="site-gallery-grid site-espacos-gallery-grid">
                            @foreach($galeria as $index => $midia)
                                <button type="button" class="site-gallery-button" @click="show({{ $index }})">
                                    <img src="{{ site_image_url($midia['src'], 'gallery') }}" alt="{{ $midia['alt'] }}" class="site-gallery-image" loading="lazy" decoding="async">
                                </button>
                            @endforeach
                        </div>

                        <div x-show="open" x-cloak class="site-lightbox" @click.self="close()" x-transition.opacity>
                            <div class="site-lightbox-frame">
                                <button type="button" class="site-lightbox-close" @click="close()" aria-label="Fechar galeria">&times;</button>
                                <button type="button" class="site-lightbox-arrow is-prev" @click.stop="prev()" aria-label="Foto anterior">&#8249;</button>
                                <img :src="images[index]?.src" :alt="images[index]?.alt || ''" class="site-lightbox-image">
                                <button type="button" class="site-lightbox-arrow is-next" @click.stop="next()" aria-label="Próxima foto">&#8250;</button>
                            </div>
                        </div>
                    </section>
                @else
                    <section class="site-surface-soft site-content-block">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $emptyTitle,
                            'editorPage' => 'site.museus.show',
                            'editorKey' => 'empty_state',
                            'editorLabel' => 'Estado vazio da galeria do museu',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['titulo', 'lead'],
                            'editableTranslation' => $emptyTranslation,
                            'editableStatus' => $museuShowBlocks['empty_state']?->status ?? 'publicado',
                            'editableFallback' => [
                                'titulo' => 'Sem imagens publicadas',
                                'lead' => 'Este museu ainda não possui galeria pública disponível.',
                            ],
                        ])
                        <div class="site-empty-state">
                            <p class="site-empty-state-title">{{ $emptyTitle }}</p>
                            <p class="site-empty-state-copy">{{ $emptyCopy }}</p>
                        </div>
                    </section>
                @endif
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    @include('site.partials._content_editor', [
                        'editorTitle' => $infoTitle,
                        'editorPage' => 'site.museus.show',
                        'editorKey' => 'info_section',
                        'editorLabel' => 'Seção de informações do museu',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline-compact',
                        'editorTriggerLabel' => 'Editar texto',
                        'editorFields' => ['eyebrow', 'titulo', 'lead'],
                        'editableTranslation' => $infoTranslation,
                        'editableStatus' => $museuShowBlocks['info_section']?->status ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => 'Informações',
                            'titulo' => 'Dados públicos',
                            'lead' => 'Tudo o que ajuda a decidir se este museu é adequado para sua visita.',
                        ],
                    ])

                    @if($editMuseuHref)
                        <div class="site-inline-actions">
                            <a href="{{ $editMuseuHref }}" class="site-button-secondary">Editar dados do museu</a>
                        </div>
                    @endif

                    <x-section-head :eyebrow="$infoEyebrow" :title="$infoTitle" :subtitle="$infoSubtitle" />

                    @if($localizacao->isEmpty())
                        <div class="site-empty-state">
                            <p class="site-empty-state-copy">Os dados públicos deste museu ainda estão em atualização.</p>
                        </div>
                    @else
                        <div class="site-location-card-list">
                            @foreach($localizacao as $item)
                                <div class="site-location-card">
                                    <span class="site-location-card-label">{{ $item['label'] }}</span>
                                    <strong class="site-location-card-value">{{ $item['value'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($espaco->maps_url)
                        <div class="site-inline-actions">
                            <a href="{{ $espaco->maps_url }}" target="_blank" rel="noopener noreferrer" class="site-button-secondary">Abrir rota</a>
                        </div>
                    @endif
                </section>

                @if($espaco->horarios->count())
                    <section class="site-surface-soft site-content-block">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $scheduleTitle,
                            'editorPage' => 'site.museus.show',
                            'editorKey' => 'schedule_section',
                            'editorLabel' => 'Seção de horários do museu',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['eyebrow', 'titulo', 'lead'],
                            'editableTranslation' => $scheduleTranslation,
                            'editableStatus' => $museuShowBlocks['schedule_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => 'Grade semanal',
                                'titulo' => 'Horários disponíveis',
                                'lead' => 'Consulte dias, faixas de visita e observações publicadas pelo coordenador.',
                            ],
                        ])

                        @if($editMuseuHref)
                            <div class="site-inline-actions">
                                <a href="{{ $editMuseuHref }}" class="site-button-secondary">Editar horarios</a>
                            </div>
                        @endif

                        <x-section-head :eyebrow="$scheduleEyebrow" :title="$scheduleTitle" :subtitle="$scheduleSubtitle" />

                        <div class="site-espacos-detail-schedule">
                            @foreach($espaco->horarios as $horario)
                                <div class="site-espacos-schedule-item site-espacos-schedule-item--stacked">
                                    <div>
                                        <div class="site-espacos-schedule-day">{{ $horario->dia_label }}</div>
                                        <div class="site-espacos-schedule-time">{{ $horario->faixa_label }}</div>
                                    </div>

                                    @if(!is_null($horario->vagas))
                                        <span class="site-filter-chip">{{ $horario->vagas }} vaga(s)</span>
                                    @endif
                                </div>

                                @if($horario->observacao)
                                    <p class="site-espacos-schedule-note">{{ $horario->observacao }}</p>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($espaco->agendamento_disponivel)
                    <section class="site-surface site-content-block site-espacos-booking-cta">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $bookingTitle,
                            'editorPage' => 'site.museus.show',
                            'editorKey' => 'booking_section',
                            'editorLabel' => 'Seção de agendamento do museu',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['eyebrow', 'titulo', 'lead'],
                            'editableTranslation' => $bookingTranslation,
                            'editableStatus' => $museuShowBlocks['booking_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => 'Agendamento',
                                'titulo' => 'Solicite sua visita',
                                'lead' => 'Use o formulário público ou fale direto no WhatsApp do atendimento cadastrado pelo coordenador.',
                            ],
                        ])

                        @if($editMuseuHref)
                            <div class="site-inline-actions">
                                <a href="{{ $editMuseuHref }}" class="site-button-secondary">Editar agendamento</a>
                            </div>
                        @endif

                        <x-section-head :eyebrow="$bookingEyebrow" :title="$bookingTitle" :subtitle="$bookingSubtitle" />

                        @if($espaco->agendamento_instrucoes)
                            <p class="site-card-list-summary">{{ $espaco->agendamento_instrucoes }}</p>
                        @endif

                        <div class="site-inline-actions site-espacos-booking-actions">
                            @if($agendamentoWhatsappHref)
                                <a href="{{ $agendamentoWhatsappHref }}" target="_blank" rel="noopener noreferrer" class="site-button-primary site-whatsapp-button">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="site-whatsapp-button-icon">
                                        <path d="M19.05 4.94A9.9 9.9 0 0 0 12.03 2C6.57 2 2.13 6.43 2.13 11.89c0 1.75.46 3.47 1.34 4.98L2 22l5.27-1.38a9.9 9.9 0 0 0 4.76 1.21h.01c5.46 0 9.9-4.43 9.9-9.89 0-2.64-1.03-5.12-2.89-7zM12.04 20.1h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.13.82.84-3.05-.2-.31a8.14 8.14 0 0 1-1.26-4.35c0-4.5 3.67-8.17 8.19-8.17 2.18 0 4.23.84 5.77 2.38a8.1 8.1 0 0 1 2.39 5.79c0 4.5-3.68 8.16-8.11 8.16zm4.48-6.1c-.25-.12-1.47-.72-1.7-.8-.23-.09-.4-.12-.57.12-.17.25-.65.8-.8.96-.15.17-.3.19-.56.07-.25-.13-1.07-.39-2.03-1.24-.75-.67-1.26-1.48-1.41-1.73-.15-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.14.17-.24.25-.4.08-.17.04-.31-.02-.43-.06-.12-.57-1.37-.78-1.88-.21-.5-.42-.43-.57-.44h-.49c-.17 0-.43.06-.66.31-.23.25-.87.85-.87 2.08s.89 2.42 1.02 2.58c.12.17 1.75 2.67 4.24 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.55.1.47-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.16-.48-.28z"/>
                                    </svg>
                                    <span>Fazer agendamento</span>
                                </a>
                            @endif
                            <a href="{{ localized_route('site.museus.agendar', ['espaco' => $espaco->slug]) }}" class="site-button-secondary">Abrir formulário</a>
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </section>
</div>
@endsection

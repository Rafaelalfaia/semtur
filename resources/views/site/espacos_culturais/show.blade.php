@extends('site.layouts.app')

@section('title', $espaco->nome . ' • ' . $espaco->tipo_label)
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) ($espaco->resumo ?: $espaco->descricao)), 160))
@section('meta.image', $espaco->capa_url ?: (optional($espaco->midias->first())->url ?: asset('imagens/altamira.jpg')))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;

    $fallback = asset('imagens/altamira.jpg');
    $capa = $espaco->capa_url ?: optional($espaco->midias->first())->url ?: $fallback;
    $galeria = collect($espaco->midias ?? [])->map(fn ($midia) => [
        'src' => $midia->url,
        'alt' => $midia->alt ?: $espaco->nome,
    ])->values();

    $localizacao = collect([
        ['label' => 'Tipo', 'value' => $espaco->tipo_label],
        ['label' => 'Endereco', 'value' => $espaco->endereco],
        ['label' => 'Bairro', 'value' => $espaco->bairro],
        ['label' => 'Cidade', 'value' => $espaco->cidade ?: 'Altamira'],
    ])->filter(fn ($item) => filled($item['value']))->values();

    $agendamentoWhatsappHref = null;
    if ($espaco->agendamento_disponivel && filled($espaco->agendamento_whatsapp_phone)) {
        $mensagemAgendamento = implode("\n", array_filter([
            'Ola! Gostaria de fazer um agendamento para visitar '.$espaco->nome.'.',
            'Tipo: '.$espaco->tipo_label,
            filled($espaco->cidade) ? 'Cidade: '.$espaco->cidade : 'Cidade: Altamira',
            'Pode me orientar sobre disponibilidade e proximos passos?',
        ]));

        $agendamentoWhatsappHref = 'https://wa.me/'.$espaco->agendamento_whatsapp_phone.'?text='.rawurlencode($mensagemAgendamento);
    }
@endphp

<div class="site-page site-page-shell site-espacos-page">
    @include('site.partials._page_hero', [
        'backHref' => Route::has('site.museus') ? localized_route('site.museus') : url()->previous(),
        'breadcrumbs' => [
            ['label' => 'Inicio', 'href' => localized_route('site.home')],
            ['label' => 'Museus e teatros', 'href' => Route::has('site.museus') ? localized_route('site.museus') : null],
            ['label' => $espaco->nome],
        ],
        'badge' => $espaco->tipo_label,
        'title' => $espaco->nome,
        'subtitle' => 'Informacoes publicas, grade semanal e orientacoes para agendar visitas ao espaco cultural.',
        'meta' => [
            $espaco->bairro,
            $espaco->cidade ?: 'Altamira',
            $espaco->agendamento_disponivel ? 'Agendamento disponivel' : 'Sem agendamento online',
        ],
        'primaryActionLabel' => $agendamentoWhatsappHref ? 'Fazer agendamento' : ($espaco->agendamento_disponivel ? 'Agendar visita' : null),
        'primaryActionHref' => $agendamentoWhatsappHref ?: ($espaco->agendamento_disponivel ? localized_route('site.museus.agendar', ['espaco' => $espaco->slug]) : null),
        'secondaryActionLabel' => $espaco->maps_url ? __('ui.common.see_on_map') : __('ui.common.back_to_listing'),
        'secondaryActionHref' => $espaco->maps_url ?: localized_route('site.museus'),
        'image' => $capa,
        'imageAlt' => $espaco->nome,
    ])

    <section class="site-section">
        <div class="site-editorial-layout site-espacos-layout">
            <div class="site-editorial-main">
                <section class="site-surface site-content-block">
                    <div class="site-detail-profile site-espacos-detail-profile">
                        <img src="{{ site_image_url($capa, 'avatar') }}" alt="{{ $espaco->nome }}" class="site-detail-avatar" loading="lazy" decoding="async">
                        <div>
                            <x-section-head
                                eyebrow="Sobre"
                                title="Planeje a visita"
                                subtitle="Museus e o teatro municipal entram aqui com leitura simples, horarios claros e caminho direto para o agendamento."
                            />
                        </div>
                    </div>

                    <div class="site-detail-copy site-prose">
                        {!! $espaco->descricao ? nl2br(e($espaco->descricao)) : '<p>Este espaco ainda nao tem uma apresentacao publica detalhada.</p>' !!}
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
                        <x-section-head
                            eyebrow="Galeria"
                            title="Imagens do espaco"
                            subtitle="Abra as fotos para ver melhor o ambiente e navegar entre as imagens publicadas."
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
                                <button type="button" class="site-lightbox-arrow is-next" @click.stop="next()" aria-label="Proxima foto">&#8250;</button>
                            </div>
                        </div>
                    </section>
                @endif
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    <x-section-head eyebrow="Informacoes" title="Dados publicos" subtitle="Tudo o que ajuda a decidir se este espaco e adequado para sua visita." />

                    @if($localizacao->isEmpty())
                        <div class="site-empty-state">
                            <p class="site-empty-state-copy">Os dados publicos deste espaco ainda estao em atualizacao.</p>
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
                        <x-section-head eyebrow="Grade semanal" title="Horarios disponiveis" subtitle="Consulte dias, faixas de visita e observacoes publicadas pelo coordenador." />

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
                        <x-section-head eyebrow="Agendamento" title="Solicite sua visita" subtitle="Use o formulario publico ou fale direto no WhatsApp do atendimento cadastrado pelo coordenador." />

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
                            <a href="{{ localized_route('site.museus.agendar', ['espaco' => $espaco->slug]) }}" class="site-button-secondary">Abrir formulario</a>
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </section>
</div>
@endsection

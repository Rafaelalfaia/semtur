@extends('site.layouts.app')

@section('title', 'Agendar visita • ' . $espaco->nome)
@section('meta.description', 'Solicite o agendamento de visita para ' . $espaco->nome . '.')

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;

    $capa = $espaco->capa_url ?: optional($espaco->midias()->first())->url ?: asset('imagens/altamira.jpg');
    $agendamentoWhatsappHref = null;
    if ($espaco->agendamento_disponivel && filled($espaco->agendamento_whatsapp_phone)) {
        $mensagemAgendamento = implode("\n", array_filter([
            'Ola! Gostaria de fazer um agendamento para visitar '.$espaco->nome.'.',
            'Tipo: '.$espaco->tipo_label,
            filled($espaco->cidade) ? 'Cidade: '.$espaco->cidade : 'Cidade: Altamira',
            'Pode me orientar sobre disponibilidade e próximos passos?',
        ]));

        $agendamentoWhatsappHref = 'https://wa.me/'.$espaco->agendamento_whatsapp_phone.'?text='.rawurlencode($mensagemAgendamento);
    }
@endphp

<div class="site-page site-page-shell site-espacos-page">
    @include('site.partials._page_hero', [
        'backHref' => route('site.museus.show', $espaco->slug),
        'breadcrumbs' => [
            ['label' => 'Início', 'href' => Route::has('site.home') ? route('site.home') : url('/')],
            ['label' => 'Museus e teatros', 'href' => Route::has('site.museus') ? route('site.museus') : null],
            ['label' => $espaco->nome, 'href' => route('site.museus.show', $espaco->slug)],
            ['label' => 'Agendar visita'],
        ],
        'badge' => 'Agendamento',
        'title' => $espaco->nome,
        'subtitle' => 'Preencha os dados do grupo e escolha a melhor data para solicitar a visita ao museu ou ao teatro municipal.',
        'meta' => [
            $espaco->tipo_label,
            $espaco->cidade ?: 'Altamira',
            $horarios->count().' horarios cadastrados',
        ],
        'primaryActionLabel' => $agendamentoWhatsappHref ? 'Fazer agendamento' : null,
        'primaryActionHref' => $agendamentoWhatsappHref,
        'secondaryActionLabel' => 'Ver espaco',
        'secondaryActionHref' => route('site.museus.show', $espaco->slug),
        'image' => $capa,
        'imageAlt' => $espaco->nome,
        'compact' => true,
    ])

    <section class="site-section">
        <div class="site-editorial-layout site-espacos-layout">
            <div class="site-editorial-main">
                <section class="site-surface site-content-block">
                    <x-section-head
                        eyebrow="Formulario"
                        title="Solicite a visita"
                        subtitle="Apos o envio, o sistema gera um protocolo para voce acompanhar a solicitacao."
                    />

                    @if ($errors->any())
                        <div class="site-espacos-form-alert">
                            <div class="site-espacos-form-alert-title">Revise os campos abaixo</div>
                            <ul class="site-espacos-form-alert-list">
                                @foreach ($errors->all() as $erro)
                                    <li>{{ $erro }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form
                        action="{{ route('site.museus.agendar.store', $espaco->slug) }}"
                        method="POST"
                        x-data="{
                            dataVisita: '{{ old('data_visita') }}',
                            horarioSelecionado: '{{ old('espaco_cultural_horario_id') }}',
                            horarios: @js($horarios->map(fn($h) => [
                                'id' => $h->id,
                                'dia_semana' => $h->dia_semana,
                                'dia_label' => $h->dia_label,
                                'faixa_label' => $h->faixa_label,
                                'vagas' => $h->vagas,
                                'observacao' => $h->observacao,
                            ])->values()),
                            dayOfWeek() {
                                if (!this.dataVisita) return null;
                                const date = new Date(this.dataVisita + 'T12:00:00');
                                return date.getDay();
                            },
                            horariosFiltrados() {
                                const dow = this.dayOfWeek();
                                if (dow === null) return [];
                                return this.horarios.filter(h => Number(h.dia_semana) === Number(dow));
                            }
                        }"
                        class="site-espacos-form-grid"
                    >
                        @csrf

                        <div>
                            <label class="site-espacos-label">Nome completo</label>
                            <input type="text" name="nome" value="{{ old('nome') }}" class="site-espacos-input" required>
                        </div>

                        <div>
                            <label class="site-espacos-label">Telefone</label>
                            <input type="text" name="telefone" value="{{ old('telefone') }}" class="site-espacos-input" placeholder="(93) 99999-9999" required>
                        </div>

                        <div>
                            <label class="site-espacos-label">E-mail</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="site-espacos-input">
                        </div>

                        <div>
                            <label class="site-espacos-label">Quantidade de visitantes</label>
                            <input type="number" min="1" max="999" name="qtd_visitantes" value="{{ old('qtd_visitantes', 1) }}" class="site-espacos-input" required>
                        </div>

                        <div>
                            <label class="site-espacos-label">Data da visita</label>
                            <input type="date" name="data_visita" x-model="dataVisita" value="{{ old('data_visita') }}" min="{{ now()->toDateString() }}" class="site-espacos-input" required>
                        </div>

                        <div>
                            <label class="site-espacos-label">Horário</label>
                            <select name="espaco_cultural_horario_id" x-model="horarioSelecionado" class="site-espacos-input" required>
                                <option value="">Selecione</option>
                                <template x-for="horario in horariosFiltrados()" :key="horario.id">
                                    <option :value="horario.id" x-text="`${horario.dia_label} • ${horario.faixa_label}`"></option>
                                </template>
                            </select>

                            <template x-if="dataVisita && horariosFiltrados().length === 0">
                                <p class="site-espacos-form-help">Não há horários cadastrados para o dia selecionado.</p>
                            </template>
                        </div>

                        <div class="site-espacos-form-span">
                            <label class="site-espacos-label">Observação</label>
                            <textarea name="observacao_visitante" rows="5" class="site-espacos-input site-espacos-textarea" placeholder="Informe detalhes importantes sobre a visita">{{ old('observacao_visitante') }}</textarea>
                        </div>

                        <div class="site-inline-actions site-espacos-form-actions site-espacos-form-span">
                            <a href="{{ route('site.museus.show', $espaco->slug) }}" class="site-button-secondary">{{ __('ui.common.back') }}</a>
                            @if($agendamentoWhatsappHref)
                                <a href="{{ $agendamentoWhatsappHref }}" target="_blank" rel="noopener noreferrer" class="site-button-secondary site-whatsapp-button site-whatsapp-button--secondary">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="site-whatsapp-button-icon">
                                        <path d="M19.05 4.94A9.9 9.9 0 0 0 12.03 2C6.57 2 2.13 6.43 2.13 11.89c0 1.75.46 3.47 1.34 4.98L2 22l5.27-1.38a9.9 9.9 0 0 0 4.76 1.21h.01c5.46 0 9.9-4.43 9.9-9.89 0-2.64-1.03-5.12-2.89-7zM12.04 20.1h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.13.82.84-3.05-.2-.31a8.14 8.14 0 0 1-1.26-4.35c0-4.5 3.67-8.17 8.19-8.17 2.18 0 4.23.84 5.77 2.38a8.1 8.1 0 0 1 2.39 5.79c0 4.5-3.68 8.16-8.11 8.16zm4.48-6.1c-.25-.12-1.47-.72-1.7-.8-.23-.09-.4-.12-.57.12-.17.25-.65.8-.8.96-.15.17-.3.19-.56.07-.25-.13-1.07-.39-2.03-1.24-.75-.67-1.26-1.48-1.41-1.73-.15-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.14.17-.24.25-.4.08-.17.04-.31-.02-.43-.06-.12-.57-1.37-.78-1.88-.21-.5-.42-.43-.57-.44h-.49c-.17 0-.43.06-.66.31-.23.25-.87.85-.87 2.08s.89 2.42 1.02 2.58c.12.17 1.75 2.67 4.24 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.55.1.47-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.16-.48-.28z"/>
                                    </svg>
                                    <span>Fazer agendamento</span>
                                </a>
                            @endif
                            <button type="submit" class="site-button-primary">Enviar solicitacao</button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    <x-section-head eyebrow="Espaço" title="Resumo rápido" subtitle="Contexto do local para revisar antes de concluir o pedido." />

                    <div class="site-location-card-list">
                        <div class="site-location-card">
                            <span class="site-location-card-label">Espaco</span>
                            <strong class="site-location-card-value">{{ $espaco->nome }}</strong>
                        </div>
                        <div class="site-location-card">
                            <span class="site-location-card-label">Tipo</span>
                            <strong class="site-location-card-value">{{ $espaco->tipo_label }}</strong>
                        </div>
                        @if($espaco->endereco)
                            <div class="site-location-card">
                                <span class="site-location-card-label">Endereco</span>
                                <strong class="site-location-card-value">{{ $espaco->endereco }}</strong>
                            </div>
                        @endif
                    </div>

                    @if ($espaco->agendamento_instrucoes)
                        <div class="site-espacos-note-card">
                            <span class="site-espacos-note-label">Instruções</span>
                            <p class="site-card-list-summary">{{ $espaco->agendamento_instrucoes }}</p>
                        </div>
                    @endif
                </section>

                @if ($horarios->count())
                    <section class="site-surface-soft site-content-block">
                        <x-section-head eyebrow="Grade semanal" title="Horários cadastrados" subtitle="A seleção de horário no formulário segue exatamente esta grade." />

                        <div class="site-espacos-detail-schedule">
                            @foreach ($horarios as $horario)
                                <div class="site-espacos-schedule-item site-espacos-schedule-item--stacked">
                                    <div>
                                        <div class="site-espacos-schedule-day">{{ $horario->dia_label }}</div>
                                        <div class="site-espacos-schedule-time">{{ $horario->faixa_label }}</div>
                                    </div>

                                    @if (!is_null($horario->vagas))
                                        <span class="site-filter-chip">{{ $horario->vagas }} vaga(s)</span>
                                    @endif
                                </div>

                                @if ($horario->observacao)
                                    <p class="site-espacos-schedule-note">{{ $horario->observacao }}</p>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </section>
</div>
@endsection



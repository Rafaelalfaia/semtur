@extends('site.layouts.app')

@section('title', 'Status da solicita��o � ' . $agendamento->protocolo)
@section('meta.description', 'Acompanhe o status da solicita��o de visita ' . $agendamento->protocolo . '.')

@section('site.content')
@php
    $statusAtual = $statusLabels[$agendamento->status] ?? ucfirst(str_replace('_', ' ', $agendamento->status));
@endphp

<section class="bg-slate-950 text-white">
    <div class="mx-auto w-full max-w-[1200px] px-4 py-14 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-sky-200">
                Protocolo {{ $agendamento->protocolo }}
            </span>

            <h1 class="mt-5 text-3xl font-bold tracking-tight sm:text-5xl">
                Solicita��o registrada
            </h1>

            <p class="mt-4 text-sm leading-7 text-slate-300 sm:text-base">
                Aqui voc� pode acompanhar o status da sua solicita��o de visita e continuar o atendimento pelo WhatsApp, quando dispon�vel.
            </p>
        </div>
    </div>
</section>

<section class="bg-slate-50">
    <div class="mx-auto grid w-full max-w-[1200px] grid-cols-1 gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_340px] lg:px-8">
        <div class="space-y-6">
            @if (session('ok'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('ok') }}
                </div>
            @endif

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900">Resumo da solicita��o</h2>
                        <p class="mt-2 text-sm text-slate-500">Confira os dados enviados no formul�rio.</p>
                    </div>

                    <span class="rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em]
                        @if($agendamento->status === 'confirmado') bg-emerald-50 text-emerald-700
                        @elseif($agendamento->status === 'cancelado' || $agendamento->status === 'expirado') bg-red-50 text-red-700
                        @elseif($agendamento->status === 'concluido') bg-sky-50 text-sky-700
                        @else bg-amber-50 text-amber-700 @endif">
                        {{ $statusAtual }}
                    </span>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Espa�o</div>
                        <div class="mt-1 font-medium text-slate-900">{{ $agendamento->espaco?->nome }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Tipo</div>
                        <div class="mt-1 font-medium text-slate-900">{{ $agendamento->espaco?->tipo_label }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Data da visita</div>
                        <div class="mt-1 font-medium text-slate-900">
                            {{ optional($agendamento->data_visita)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Hor�rio</div>
                        <div class="mt-1 font-medium text-slate-900">
                            {{ $agendamento->horario?->faixa_label ?: 'N�o informado' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Respons�vel</div>
                        <div class="mt-1 font-medium text-slate-900">{{ $agendamento->nome }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Telefone</div>
                        <div class="mt-1 font-medium text-slate-900">{{ $agendamento->telefone }}</div>
                    </div>

                    @if ($agendamento->email)
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">E-mail</div>
                            <div class="mt-1 font-medium text-slate-900">{{ $agendamento->email }}</div>
                        </div>
                    @endif

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Quantidade de visitantes</div>
                        <div class="mt-1 font-medium text-slate-900">{{ $agendamento->qtd_visitantes }}</div>
                    </div>
                </div>

                @if ($agendamento->observacao_visitante)
                    <div class="mt-6 rounded-2xl bg-slate-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Observa��o enviada</div>
                        <div class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">
                            {{ $agendamento->observacao_visitante }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold tracking-tight text-slate-900">Pr�ximos passos</h2>

                <div class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                    <p>
                        Guarde o protocolo <span class="font-semibold text-slate-900">{{ $agendamento->protocolo }}</span> para consultar esta solicita��o.
                    </p>

                    <p>
                        O status atual � <span class="font-semibold text-slate-900">{{ $statusAtual }}</span>.
                    </p>

                    @if ($agendamento->espaco?->agendamento_instrucoes)
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Orienta��es</div>
                            <div class="mt-2 text-sm leading-6 text-slate-600">
                                {{ $agendamento->espaco->agendamento_instrucoes }}
                            </div>
                        </div>
                    @endif
                </div>

                @if ($agendamento->whatsapp_link)
                    <div class="mt-6">
                        <a
                            href="{{ route('site.museus.agendamentos.whatsapp', $agendamento->protocolo) }}"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700"
                        >
                            Continuar pelo WhatsApp
                        </a>
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold tracking-tight text-slate-900">Navega��o</h2>

                <div class="mt-4 flex flex-col gap-3">
                    @if ($agendamento->espaco)
                        <a
                            href="{{ route('site.museus.show', $agendamento->espaco->slug) }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            {{ __('ui.common.back_to_space') }}
                        </a>
                    @endif

                    <a
                        href="{{ route('site.museus') }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Ver outros espa�os
                    </a>
                </div>
            </div>
        </aside>
    </div>
</section>
@endsection

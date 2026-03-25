@extends('site.layouts.app')

@section('title', 'Agendar visita • ' . $espaco->nome)
@section('meta.description', 'Solicite o agendamento de visita para ' . $espaco->nome . '.')

@section('site.content')
<section class="bg-slate-950 text-white">
    <div class="mx-auto w-full max-w-[1200px] px-4 py-14 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-sky-200">
                Agendamento de visita
            </span>

            <h1 class="mt-5 text-3xl font-bold tracking-tight sm:text-5xl">
                {{ $espaco->nome }}
            </h1>

            <p class="mt-4 text-sm leading-7 text-slate-300 sm:text-base">
                Preencha os dados abaixo para solicitar sua visita. Após o envio, o sistema irá gerar um protocolo para acompanhamento.
            </p>
        </div>
    </div>
</section>

<section class="bg-slate-50">
    <div class="mx-auto grid w-full max-w-[1200px] grid-cols-1 gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_340px] lg:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
                    <div class="font-semibold">Revise os campos abaixo:</div>
                    <ul class="mt-2 space-y-1 text-sm">
                        @foreach ($errors->all() as $erro)
                            <li>• {{ $erro }}</li>
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
                class="space-y-6"
            >
                @csrf

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Nome completo</label>
                        <input
                            type="text"
                            name="nome"
                            value="{{ old('nome') }}"
                            class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                            required
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Telefone</label>
                        <input
                            type="text"
                            name="telefone"
                            value="{{ old('telefone') }}"
                            class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                            placeholder="(93) 99999-9999"
                            required
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">E-mail</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Quantidade de visitantes</label>
                        <input
                            type="number"
                            min="1"
                            max="999"
                            name="qtd_visitantes"
                            value="{{ old('qtd_visitantes', 1) }}"
                            class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                            required
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Data da visita</label>
                        <input
                            type="date"
                            name="data_visita"
                            x-model="dataVisita"
                            value="{{ old('data_visita') }}"
                            min="{{ now()->toDateString() }}"
                            class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                            required
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Horário</label>
                        <select
                            name="espaco_cultural_horario_id"
                            x-model="horarioSelecionado"
                            class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                            required
                        >
                            <option value="">Selecione</option>

                            <template x-for="horario in horariosFiltrados()" :key="horario.id">
                                <option :value="horario.id" x-text="`${horario.dia_label} • ${horario.faixa_label}`"></option>
                            </template>
                        </select>

                        <template x-if="dataVisita && horariosFiltrados().length === 0">
                            <p class="mt-2 text-sm text-amber-700">
                                Não há horários cadastrados para o dia selecionado.
                            </p>
                        </template>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Observação</label>
                        <textarea
                            name="observacao_visitante"
                            rows="5"
                            class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                            placeholder="Informe observações importantes sobre a visita"
                        >{{ old('observacao_visitante') }}</textarea>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route('site.museus.show', $espaco->slug) }}"
                        class="inline-flex items-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Voltar
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center rounded-2xl bg-slate-900 px-6 py-3 text-sm font-medium text-white hover:bg-slate-800"
                    >
                        Enviar solicitação
                    </button>
                </div>
            </form>
        </div>

        <aside class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold tracking-tight text-slate-900">Resumo do espaço</h2>

                <div class="mt-4 space-y-4 text-sm text-slate-600">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Espaço</div>
                        <div class="mt-1 font-medium text-slate-900">{{ $espaco->nome }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Tipo</div>
                        <div class="mt-1 font-medium text-slate-900">{{ $espaco->tipo_label }}</div>
                    </div>

                    @if ($espaco->endereco)
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Endereço</div>
                            <div class="mt-1 font-medium text-slate-900">{{ $espaco->endereco }}</div>
                        </div>
                    @endif

                    @if ($espaco->agendamento_instrucoes)
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Instruções</div>
                            <div class="mt-2 text-sm leading-6 text-slate-600">
                                {{ $espaco->agendamento_instrucoes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($horarios->count())
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold tracking-tight text-slate-900">Grade semanal</h2>

                    <div class="mt-4 space-y-3">
                        @foreach ($horarios as $horario)
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="font-medium text-slate-900">
                                    {{ $horario->dia_label }} • {{ $horario->faixa_label }}
                                </div>

                                @if (!is_null($horario->vagas))
                                    <div class="mt-1 text-sm text-slate-600">
                                        {{ $horario->vagas }} vaga(s)
                                    </div>
                                @endif

                                @if ($horario->observacao)
                                    <div class="mt-1 text-sm text-slate-500">
                                        {{ $horario->observacao }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
</section>
@endsection

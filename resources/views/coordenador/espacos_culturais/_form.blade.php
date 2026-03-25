@php
    $isEdit = $espaco->exists;

    $horariosBase = old('horarios');

    if ($horariosBase === null) {
        $horariosBase = $espaco->relationLoaded('horarios')
            ? $espaco->horarios->map(fn ($h) => [
                'id' => $h->id,
                'dia_semana' => $h->dia_semana,
                'hora_inicio' => \Illuminate\Support\Str::of((string) $h->hora_inicio)->substr(0, 5),
                'hora_fim' => \Illuminate\Support\Str::of((string) $h->hora_fim)->substr(0, 5),
                'vagas' => $h->vagas,
                'observacao' => $h->observacao,
                'ativo' => (bool) $h->ativo,
                'ordem' => $h->ordem ?? 0,
            ])->values()->all()
            : [];
    }

    if (empty($horariosBase)) {
        $horariosBase = [[
            'id' => null,
            'dia_semana' => 1,
            'hora_inicio' => '08:00',
            'hora_fim' => '09:00',
            'vagas' => null,
            'observacao' => null,
            'ativo' => true,
            'ordem' => 0,
        ]];
    }

    $midiasAtuais = $espaco->relationLoaded('midias') ? $espaco->midias : collect();
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
        <div class="font-semibold mb-2">Revise os campos abaixo:</div>
        <ul class="list-disc pl-5 space-y-1 text-sm">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="ui-espaco-form space-y-8">
    {{-- Bloco principal --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Informações principais</h2>
            <p class="text-sm text-slate-500 mt-1">Dados editoriais do espaço cultural.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 px-6 py-6 lg:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Tipo</label>
                <select name="tipo" class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                    <option value="museu" @selected(old('tipo', $espaco->tipo) === 'museu')>Museu</option>
                    <option value="teatro" @selected(old('tipo', $espaco->tipo) === 'teatro')>Teatro</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Status</label>
                <select name="status" class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                    <option value="rascunho" @selected(old('status', $espaco->status) === 'rascunho')>Rascunho</option>
                    <option value="publicado" @selected(old('status', $espaco->status) === 'publicado')>Publicado</option>
                    <option value="arquivado" @selected(old('status', $espaco->status) === 'arquivado')>Arquivado</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">Nome</label>
                <input
                    type="text"
                    name="nome"
                    value="{{ old('nome', $espaco->nome) }}"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="Ex.: Museu Municipal João..."
                >
            </div>

            <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">Resumo</label>
                <input
                    type="text"
                    name="resumo"
                    value="{{ old('resumo', $espaco->resumo) }}"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    maxlength="255"
                    placeholder="Texto curto para cards e chamadas"
                >
            </div>

            <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">Descrição</label>
                <textarea
                    name="descricao"
                    rows="6"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="Conte a história, importância, acervo, programação ou orientações do espaço"
                >{{ old('descricao', $espaco->descricao) }}</textarea>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Ordem</label>
                <input
                    type="number"
                    min="0"
                    name="ordem"
                    value="{{ old('ordem', $espaco->ordem ?? 0) }}"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                >
            </div>

            <div class="flex items-end">
                <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600 w-full">
                    Ao publicar, o sistema exige capa, conteúdo e localização mínima.
                </div>
            </div>
        </div>
    </div>

    {{-- Localização --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Localização</h2>
            <p class="text-sm text-slate-500 mt-1">Mapa, endereço e coordenadas.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 px-6 py-6 lg:grid-cols-2">
            <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">Link do Google Maps</label>
                <input
                    type="url"
                    name="maps_url"
                    value="{{ old('maps_url', $espaco->maps_url) }}"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="Cole aqui o link do Maps"
                >
            </div>

            <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">Endereço</label>
                <input
                    type="text"
                    name="endereco"
                    value="{{ old('endereco', $espaco->endereco) }}"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="Rua, número, complemento"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Bairro</label>
                <input
                    type="text"
                    name="bairro"
                    value="{{ old('bairro', $espaco->bairro) }}"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Cidade</label>
                <input
                    type="text"
                    name="cidade"
                    value="{{ old('cidade', $espaco->cidade ?: 'Altamira') }}"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Latitude</label>
                <input
                    type="text"
                    name="lat"
                    value="{{ old('lat', $espaco->lat) }}"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="-3.204..."
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Longitude</label>
                <input
                    type="text"
                    name="lng"
                    value="{{ old('lng', $espaco->lng) }}"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="-52.206..."
                >
            </div>
        </div>
    </div>

    {{-- Mídia --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Capa e galeria</h2>
            <p class="text-sm text-slate-500 mt-1">Imagens do espaço cultural.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 px-6 py-6 lg:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Imagem de capa</label>
                <input
                    type="file"
                    name="capa"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="block w-full rounded-2xl border border-slate-300 bg-white text-sm text-slate-700"
                >

                @if ($espaco->capa_url)
                    <div class="mt-4 rounded-2xl border border-slate-200 p-3">
                        <img src="{{ $espaco->capa_url }}" alt="Capa atual" class="h-48 w-full rounded-xl object-cover">
                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-red-700">
                            <input type="checkbox" name="remover_capa" value="1" class="rounded border-slate-300">
                            Remover capa atual
                        </label>
                    </div>
                @endif
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Galeria</label>
                <input
                    type="file"
                    name="galeria[]"
                    multiple
                    accept=".jpg,.jpeg,.png,.webp"
                    class="block w-full rounded-2xl border border-slate-300 bg-white text-sm text-slate-700"
                >

                @if ($midiasAtuais->count())
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        @foreach ($midiasAtuais as $midia)
                            <label class="rounded-2xl border border-slate-200 p-2">
                                <img src="{{ $midia->url }}" alt="{{ $midia->alt ?: $espaco->nome }}" class="h-28 w-full rounded-xl object-cover">
                                <div class="mt-2 flex items-center gap-2 text-xs text-red-700">
                                    <input type="checkbox" name="remover_midias[]" value="{{ $midia->id }}" class="rounded border-slate-300">
                                    Remover
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Horários --}}
    <div
        class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden"
        x-data="{
            horarios: @js($horariosBase),
            dias: @js($diasSemana),
            addHorario() {
                this.horarios.push({
                    id: null,
                    dia_semana: 1,
                    hora_inicio: '08:00',
                    hora_fim: '09:00',
                    vagas: null,
                    observacao: null,
                    ativo: true,
                    ordem: this.horarios.length
                });
            },
            removeHorario(index) {
                this.horarios.splice(index, 1);
                this.horarios = this.horarios.map((item, idx) => ({ ...item, ordem: idx }));
            }
        }"
    >
        <div class="border-b border-slate-200 px-6 py-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Horários e vagas</h2>
                <p class="text-sm text-slate-500 mt-1">Grade semanal do espaço.</p>
            </div>

            <button
                type="button"
                @click="addHorario()"
                class="inline-flex items-center rounded-2xl bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700"
            >
                + Adicionar horário
            </button>
        </div>

        <div class="space-y-4 px-6 py-6">
            <template x-for="(horario, index) in horarios" :key="index">
                <div class="rounded-2xl border border-slate-200 p-4">
                    <input type="hidden" :name="`horarios[${index}][id]`" x-model="horario.id">
                    <input type="hidden" :name="`horarios[${index}][ordem]`" x-model="horario.ordem">

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Dia</label>
                            <select :name="`horarios[${index}][dia_semana]`" x-model="horario.dia_semana"
                                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                                <template x-for="(label, dia) in dias" :key="dia">
                                    <option :value="dia" x-text="label"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Início</label>
                            <input type="time" :name="`horarios[${index}][hora_inicio]`" x-model="horario.hora_inicio"
                                   class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Fim</label>
                            <input type="time" :name="`horarios[${index}][hora_fim]`" x-model="horario.hora_fim"
                                   class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Vagas</label>
                            <input type="number" min="1" :name="`horarios[${index}][vagas]`" x-model="horario.vagas"
                                   class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                                   placeholder="Opcional">
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Observação</label>
                            <input type="text" :name="`horarios[${index}][observacao]`" x-model="horario.observacao"
                                   class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                                   placeholder="Ex.: visita escolar, acessível, etc.">
                        </div>

                        <div class="lg:col-span-5">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" :name="`horarios[${index}][ativo]`" value="1" x-model="horario.ativo"
                                       class="rounded border-slate-300">
                                Horário ativo
                            </label>
                        </div>

                        <div class="flex items-end justify-end">
                            <button type="button" @click="removeHorario(index)"
                                    class="inline-flex items-center rounded-2xl border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                                Remover
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Agendamento --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Configuração de agendamento</h2>
            <p class="text-sm text-slate-500 mt-1">Atendimento e instruções para solicitação pública.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 px-6 py-6 lg:grid-cols-2">
            <div class="lg:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="agendamento_ativo" value="1"
                           @checked(old('agendamento_ativo', $espaco->agendamento_ativo))
                           class="rounded border-slate-300">
                    Habilitar agendamento público
                </label>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Nome do contato</label>
                <input
                    type="text"
                    name="agendamento_contato_nome"
                    value="{{ old('agendamento_contato_nome', $espaco->agendamento_contato_nome) }}"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="Ex.: Coordenação de Museus"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Rótulo do contato</label>
                <input
                    type="text"
                    name="agendamento_contato_label"
                    value="{{ old('agendamento_contato_label', $espaco->agendamento_contato_label) }}"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="Ex.: Agendamentos"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">WhatsApp</label>
                <input
                    type="text"
                    name="agendamento_whatsapp_phone"
                    value="{{ old('agendamento_whatsapp_phone', $espaco->agendamento_whatsapp_phone) }}"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="5593999999999"
                >
            </div>

            <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">Instruções</label>
                <textarea
                    name="agendamento_instrucoes"
                    rows="4"
                    class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="Orientações visíveis ao visitante antes do envio"
                >{{ old('agendamento_instrucoes', $espaco->agendamento_instrucoes) }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('coordenador.espacos-culturais.index') }}" class="ui-btn-secondary">Voltar</a>
        <button type="submit" class="ui-btn-primary">{{ $isEdit ? 'Salvar alterações' : 'Cadastrar espaço cultural' }}</button>
    </div>
</div>

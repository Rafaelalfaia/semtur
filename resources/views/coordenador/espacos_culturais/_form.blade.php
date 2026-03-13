@php
    $statusAtual = old('status', $espaco->status ?? 'rascunho');
    $tipoAtual   = old('tipo', $espaco->tipo ?? 'museu');

    $horariosOld = old('horarios');

    if ($horariosOld === null) {
        $horariosOld = isset($espaco) && $espaco->exists
            ? $espaco->horarios
                ->sortBy([['dia_semana', 'asc'], ['hora_inicio', 'asc']])
                ->map(function ($h) {
                    return [
                        'dia_semana' => $h->dia_semana,
                        'hora_inicio' => substr((string) $h->hora_inicio, 0, 5),
                        'hora_fim' => substr((string) $h->hora_fim, 0, 5),
                        'vagas' => $h->vagas,
                        'observacao' => $h->observacao,
                        'ativo' => (int) $h->ativo,
                        'ordem' => $h->ordem,
                    ];
                })
                ->values()
                ->all()
            : [[
                'dia_semana' => 1,
                'hora_inicio' => '09:00',
                'hora_fim' => '12:00',
                'vagas' => '',
                'observacao' => '',
                'ativo' => 1,
                'ordem' => 0,
            ]];
    }

    $latSaved = $espaco->lat ?? null;
    $lngSaved = $espaco->lng ?? null;
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="block text-sm text-slate-300 mb-1">Tipo *</label>
        <select name="tipo" class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100" required>
            <option value="museu" @selected($tipoAtual === 'museu')>Museu</option>
            <option value="teatro" @selected($tipoAtual === 'teatro')>Teatro</option>
        </select>
        @error('tipo')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm text-slate-300 mb-1">Status *</label>
        <select name="status" class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100" required>
            <option value="rascunho"  @selected($statusAtual === 'rascunho')>Rascunho</option>
            <option value="publicado" @selected($statusAtual === 'publicado')>Publicado</option>
            <option value="arquivado" @selected($statusAtual === 'arquivado')>Arquivado</option>
        </select>
        @error('status')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm text-slate-300 mb-1">Nome *</label>
        <input type="text" name="nome" value="{{ old('nome', $espaco->nome ?? '') }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100" required>
        @error('nome')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm text-slate-300 mb-1">Localização (URL do Google Maps)</label>
        <input type="url" name="maps_url" value="{{ old('maps_url', $espaco->maps_url ?? '') }}"
               placeholder="https://www.google.com/maps/place/.../@-3.2059,-52.2137,16z"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
        @error('maps_url')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror

        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 mt-2">
            @if(old('maps_url', $espaco->maps_url ?? ''))
                <a href="{{ old('maps_url', $espaco->maps_url ?? '') }}" target="_blank" class="underline hover:no-underline">
                    Abrir no Maps ↗
                </a>
            @endif

            @if(!is_null($latSaved) && !is_null($lngSaved))
                <span>
                    Coordenadas salvas: {{ number_format($latSaved, 7, ',', '') }}, {{ number_format($lngSaved, 7, ',', '') }}
                </span>
            @endif
        </div>

        <p class="text-xs text-slate-400 mt-2">
            Basta colar a URL do Maps. As coordenadas serão extraídas automaticamente.
            Ao publicar, o sistema exige coordenadas válidas.
        </p>
    </div>

    <div>
        <label class="block text-sm text-slate-300 mb-1">Endereço</label>
        <input type="text" name="endereco" value="{{ old('endereco', $espaco->endereco ?? '') }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
        @error('endereco')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm text-slate-300 mb-1">Bairro</label>
        <input type="text" name="bairro" value="{{ old('bairro', $espaco->bairro ?? '') }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
        @error('bairro')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm text-slate-300 mb-1">Cidade</label>
        <input type="text" name="cidade" value="{{ old('cidade', $espaco->cidade ?? 'Altamira') }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
        @error('cidade')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm text-slate-300 mb-1">Ordem</label>
        <input type="number" min="0" name="ordem" value="{{ old('ordem', $espaco->ordem ?? 0) }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
        @error('ordem')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm text-slate-300 mb-1">Descrição</label>
        <textarea name="descricao" rows="5"
                  class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">{{ old('descricao', $espaco->descricao ?? '') }}</textarea>
        @error('descricao')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6 rounded-2xl border border-white/10 bg-slate-900/50 p-4"
     x-data="{
        dias: @js($diasSemana),
        horarios: @js(array_values($horariosOld)),
        addHorario() {
            this.horarios.push({
                dia_semana: 1,
                hora_inicio: '09:00',
                hora_fim: '12:00',
                vagas: '',
                observacao: '',
                ativo: 1,
                ordem: this.horarios.length
            });
        }
     }">

    <div class="flex items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-sm font-semibold text-slate-200 uppercase tracking-wide">Dias e horários de visita</h3>
            <p class="text-xs text-slate-400 mt-1">Essa estrutura já será a base do agendamento no site.</p>
        </div>

        <button type="button"
                @click="addHorario()"
                class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-3 py-2 text-sm text-white">
            + Adicionar horário
        </button>
    </div>

    <template x-for="(item, i) in horarios" :key="i">
        <div class="rounded-xl border border-white/10 bg-white/5 p-4 mb-3">
            <div class="grid gap-3 md:grid-cols-6">
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Dia *</label>
                    <select :name="'horarios[' + i + '][dia_semana]'"
                            x-model="item.dia_semana"
                            class="w-full rounded-lg bg-slate-900/70 border border-white/10 px-3 py-2 text-slate-100">
                        <template x-for="(label, valor) in dias" :key="valor">
                            <option :value="valor" x-text="label"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-slate-300 mb-1">Início *</label>
                    <input type="time"
                           :name="'horarios[' + i + '][hora_inicio]'"
                           x-model="item.hora_inicio"
                           class="w-full rounded-lg bg-slate-900/70 border border-white/10 px-3 py-2 text-slate-100">
                </div>

                <div>
                    <label class="block text-xs text-slate-300 mb-1">Fim *</label>
                    <input type="time"
                           :name="'horarios[' + i + '][hora_fim]'"
                           x-model="item.hora_fim"
                           class="w-full rounded-lg bg-slate-900/70 border border-white/10 px-3 py-2 text-slate-100">
                </div>

                <div>
                    <label class="block text-xs text-slate-300 mb-1">Vagas</label>
                    <input type="number"
                           min="1"
                           :name="'horarios[' + i + '][vagas]'"
                           x-model="item.vagas"
                           class="w-full rounded-lg bg-slate-900/70 border border-white/10 px-3 py-2 text-slate-100"
                           placeholder="Opcional">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-300 mb-1">Observação</label>
                    <input type="text"
                           :name="'horarios[' + i + '][observacao]'"
                           x-model="item.observacao"
                           class="w-full rounded-lg bg-slate-900/70 border border-white/10 px-3 py-2 text-slate-100"
                           placeholder="Ex.: Visita escolar, sessão guiada...">
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <input type="hidden" :name="'horarios[' + i + '][ativo]'" value="0">
                    <input type="checkbox"
                           :name="'horarios[' + i + '][ativo]'"
                           value="1"
                           :checked="Number(item.ativo) === 1"
                           @change="item.ativo = $event.target.checked ? 1 : 0"
                           class="rounded border-white/20 bg-slate-900 text-emerald-500">
                    <span class="text-xs text-slate-300">Horário ativo</span>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" :name="'horarios[' + i + '][ordem]'" :value="i">
                    <button type="button"
                            @click="horarios.splice(i, 1)"
                            x-show="horarios.length > 1"
                            class="rounded-lg bg-rose-600/20 hover:bg-rose-600/30 px-3 py-2 text-xs text-rose-200">
                        Remover
                    </button>
                </div>
            </div>
        </div>
    </template>

    @error('horarios')<p class="text-xs text-rose-300 mt-2">{{ $message }}</p>@enderror
    @error('horarios.*.dia_semana')<p class="text-xs text-rose-300 mt-2">{{ $message }}</p>@enderror
    @error('horarios.*.hora_inicio')<p class="text-xs text-rose-300 mt-2">{{ $message }}</p>@enderror
    @error('horarios.*.hora_fim')<p class="text-xs text-rose-300 mt-2">{{ $message }}</p>@enderror
</div>

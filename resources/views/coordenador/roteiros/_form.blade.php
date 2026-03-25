@php
    /** @var \App\Models\Catalogo\Roteiro $roteiro */

    $statusAtual = old('status', $roteiro->status ?? 'rascunho');
    $isEdit = ($mode ?? 'create') === 'edit';

    $coverCurrent = $roteiro->capa_url ?? null;

    $etapasForm = old('etapas');
    if ($etapasForm === null) {
        $etapasForm = [];

        if ($roteiro->exists) {
            foreach (($roteiro->etapas ?? collect()) as $etapa) {
                $etapasForm[] = [
                    'titulo' => $etapa->titulo,
                    'subtitulo' => $etapa->subtitulo,
                    'descricao' => $etapa->descricao,
                    'tipo_bloco' => $etapa->tipo_bloco,
                    'pontos' => collect($etapa->pontos ?? [])->map(function ($item) {
                        return [
                            'ponto_turistico_id' => $item->ponto_turistico_id,
                            'observacao_curta' => $item->observacao_curta,
                            'tempo_estimado_min' => $item->tempo_estimado_min,
                            'destaque' => (bool) $item->destaque,
                        ];
                    })->values()->all(),
                ];
            }
        }

        if (empty($etapasForm)) {
            $etapasForm = [[
                'titulo' => 'Manhã',
                'subtitulo' => null,
                'descricao' => null,
                'tipo_bloco' => 'manha',
                'pontos' => [[
                    'ponto_turistico_id' => null,
                    'observacao_curta' => null,
                    'tempo_estimado_min' => null,
                    'destaque' => false,
                ]],
            ]];
        }
    }

    $empresasForm = old('empresas');
    if ($empresasForm === null) {
        $empresasForm = [];

        if ($roteiro->exists) {
            foreach (($roteiro->empresasSugestao ?? collect()) as $item) {
                $empresasForm[] = [
                    'empresa_id' => $item->empresa_id,
                    'tipo_sugestao' => $item->tipo_sugestao,
                    'observacao_curta' => $item->observacao_curta,
                    'destaque' => (bool) $item->destaque,
                ];
            }
        }
    }

    $pontosOptions = collect($pontos ?? [])->map(fn ($item) => [
        'id' => (int) $item->id,
        'nome' => $item->nome,
    ])->values()->all();

    $empresasOptions = collect($empresas ?? [])->map(fn ($item) => [
        'id' => (int) $item->id,
        'nome' => $item->nome,
    ])->values()->all();

    $tiposBlocoOptions = collect($tiposBloco ?? [])->map(fn ($label, $key) => [
        'id' => $key,
        'label' => $label,
    ])->values()->all();

    $tiposSugestaoOptions = collect($tiposSugestao ?? [])->map(fn ($label, $key) => [
        'id' => $key,
        'label' => $label,
    ])->values()->all();
@endphp

@push('head')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

<div
    x-data="roteiroBuilder({
        etapasInicial: {{ \Illuminate\Support\Js::from($etapasForm) }},
        empresasInicial: {{ \Illuminate\Support\Js::from($empresasForm) }},
        pontosDisponiveis: {{ \Illuminate\Support\Js::from($pontosOptions) }},
        empresasDisponiveis: {{ \Illuminate\Support\Js::from($empresasOptions) }},
        tiposBlocoDisponiveis: {{ \Illuminate\Support\Js::from($tiposBlocoOptions) }},
        tiposSugestaoDisponiveis: {{ \Illuminate\Support\Js::from($tiposSugestaoOptions) }},
        capaAtual: {{ \Illuminate\Support\Js::from($coverCurrent) }},
    })"
    class="ui-roteiro-builder grid gap-6 xl:grid-cols-3"
>
    <div class="space-y-6 xl:col-span-2">
        <section class="rounded-2xl border border-white/10 bg-[#0F1412] p-5">
            <div class="mb-5">
                <div class="text-xs uppercase tracking-[0.18em] text-emerald-300/80">Dados gerais</div>
                <h2 class="mt-1 text-lg font-semibold text-slate-100">Identidade do roteiro</h2>
                <p class="mt-1 text-sm text-slate-400">
                    Defina o título, a duração, o perfil e a mensagem principal.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">Título *</label>
                    <input
                        type="text"
                        name="titulo"
                        value="{{ old('titulo', $roteiro->titulo ?? '') }}"
                        x-model="titulo"
                        @input="syncSlug()"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        required
                    >
                    @error('titulo')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">Slug</label>
                    <input
                        type="text"
                        name="slug"
                        value="{{ old('slug', $roteiro->slug ?? '') }}"
                        x-model="slug"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        placeholder="altamira-em-1-dia"
                    >
                    <p class="mt-1 text-xs text-slate-400">
                        Pode editar manualmente. Se deixar em branco, o sistema gera a partir do título.
                    </p>
                    @error('slug')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm text-slate-300">Status *</label>
                    <select
                        name="status"
                        x-model="status"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        required
                    >
                        <option value="rascunho">Rascunho</option>
                        <option value="publicado">Publicado</option>
                        <option value="arquivado">Arquivado</option>
                    </select>
                    @error('status')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm text-slate-300">Ordem</label>
                    <input
                        type="number"
                        min="0"
                        name="ordem"
                        value="{{ old('ordem', $roteiro->ordem ?? 0) }}"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                    >
                    @error('ordem')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm text-slate-300">Duração *</label>
                    <select
                        name="duracao_slug"
                        x-model="duracao"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        required
                    >
                        @foreach(($duracoes ?? []) as $key => $label)
                            <option value="{{ $key }}" @selected(old('duracao_slug', $roteiro->duracao_slug ?? '1_dia') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('duracao_slug')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm text-slate-300">Perfil *</label>
                    <select
                        name="perfil_slug"
                        x-model="perfil"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        required
                    >
                        @foreach(($perfis ?? []) as $key => $label)
                            <option value="{{ $key }}" @selected(old('perfil_slug', $roteiro->perfil_slug ?? 'geral') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('perfil_slug')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm text-slate-300">Público</label>
                    <input
                        type="text"
                        name="publico_label"
                        value="{{ old('publico_label', $roteiro->publico_label ?? '') }}"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        placeholder="Casais, famílias, visitante de fim de semana..."
                    >
                    @error('publico_label')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm text-slate-300">Melhor época</label>
                    <input
                        type="text"
                        name="melhor_epoca"
                        value="{{ old('melhor_epoca', $roteiro->melhor_epoca ?? '') }}"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        placeholder="Julho a novembro, período seco..."
                    >
                    @error('melhor_epoca')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm text-slate-300">Deslocamento</label>
                    <input
                        type="text"
                        name="deslocamento"
                        value="{{ old('deslocamento', $roteiro->deslocamento ?? '') }}"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        placeholder="Carro, barco, a pé..."
                    >
                    @error('deslocamento')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm text-slate-300">Intensidade</label>
                    <select
                        name="nivel_intensidade"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                    >
                        <option value="">Selecione</option>
                        <option value="leve" @selected(old('nivel_intensidade', $roteiro->nivel_intensidade ?? '') === 'leve')>Leve</option>
                        <option value="moderado" @selected(old('nivel_intensidade', $roteiro->nivel_intensidade ?? '') === 'moderado')>Moderado</option>
                        <option value="intenso" @selected(old('nivel_intensidade', $roteiro->nivel_intensidade ?? '') === 'intenso')>Intenso</option>
                    </select>
                    @error('nivel_intensidade')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">Resumo *</label>
                    <textarea
                        name="resumo"
                        rows="3"
                        x-model="resumo"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        placeholder="Uma introdução curta, convidativa e clara para o visitante."
                        required
                    >{{ old('resumo', $roteiro->resumo ?? '') }}</textarea>
                    @error('resumo')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">Descrição geral</label>
                    <textarea
                        name="descricao"
                        rows="6"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        placeholder="Conte a proposta do roteiro, a experiência que ele entrega e o contexto do percurso."
                    >{{ old('descricao', $roteiro->descricao ?? '') }}</textarea>
                    @error('descricao')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-[#0F1412] p-5">
            <div class="mb-5">
                <div class="text-xs uppercase tracking-[0.18em] text-emerald-300/80">Capa e SEO</div>
                <h2 class="mt-1 text-lg font-semibold text-slate-100">Imagem principal e metadados</h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">Imagem de capa</label>
                    <input
                        type="file"
                        name="capa"
                        accept="image/*"
                        x-ref="coverInput"
                        @change="updateCoverPreview($event)"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                    >
                    @error('capa')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror

                    <div class="mt-4 overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60">
                        <div class="relative h-52 w-full bg-slate-800">
                            <template x-if="coverPreview">
                                <img :src="coverPreview" alt="Prévia da capa" class="h-full w-full object-cover">
                            </template>

                            <template x-if="!coverPreview">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-700/30 via-slate-800 to-slate-950"></div>
                            </template>
                        </div>

                        <div class="p-3 text-xs text-slate-400">
                            A capa será usada no card do roteiro e na página pública.
                        </div>
                    </div>

                    @if($isEdit && $coverCurrent)
                        <div class="mt-3">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                                <input type="hidden" name="remover_capa" value="0">
                                <input
                                    type="checkbox"
                                    name="remover_capa"
                                    value="1"
                                    x-model="removeCurrentCover"
                                    class="rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-500"
                                >
                                Remover capa atual
                            </label>
                        </div>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">SEO title</label>
                    <input
                        type="text"
                        name="seo_title"
                        value="{{ old('seo_title', $roteiro->seo_title ?? '') }}"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                    >
                    @error('seo_title')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">SEO description</label>
                    <textarea
                        name="seo_description"
                        rows="3"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                    >{{ old('seo_description', $roteiro->seo_description ?? '') }}</textarea>
                    @error('seo_description')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-[#0F1412] p-5">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-xs uppercase tracking-[0.18em] text-emerald-300/80">Etapas do roteiro</div>
                    <h2 class="mt-1 text-lg font-semibold text-slate-100">Narrativa e percurso</h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Organize a experiência por manhã, tarde, noite ou dias do percurso.
                    </p>
                </div>

                <button
                    type="button"
                    @click="addEtapa()"
                    class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
                >
                    + Adicionar etapa
                </button>
            </div>

            @error('etapas')<p class="mb-3 text-xs text-rose-300">{{ $message }}</p>@enderror

            <div class="space-y-4">
                <template x-for="(etapa, etapaIndex) in etapas" :key="etapa._key">
                    <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03]">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/10 px-4 py-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-100">
                                    Etapa <span x-text="etapaIndex + 1"></span>
                                </div>
                                <div class="text-xs text-slate-400">
                                    <span x-text="etapa.titulo || 'Sem título'"></span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    @click="moveEtapaUp(etapaIndex)"
                                    class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
                                >
                                    Subir
                                </button>

                                <button
                                    type="button"
                                    @click="moveEtapaDown(etapaIndex)"
                                    class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
                                >
                                    Descer
                                </button>

                                <button
                                    type="button"
                                    @click="removeEtapa(etapaIndex)"
                                    class="rounded-lg border border-rose-500/20 bg-rose-500/10 px-3 py-1.5 text-xs text-rose-200 hover:bg-rose-500/15"
                                >
                                    Remover
                                </button>
                            </div>
                        </div>

                        <div class="space-y-4 p-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm text-slate-300">Título *</label>
                                    <input
                                        type="text"
                                        x-model="etapa.titulo"
                                        :name="'etapas[' + etapaIndex + '][titulo]'"
                                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                                        required
                                    >
                                    <template x-if="hasError(`etapas.${etapaIndex}.titulo`)">
                                        <p class="mt-1 text-xs text-rose-300" x-text="errorText(`etapas.${etapaIndex}.titulo`)"></p>
                                    </template>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm text-slate-300">Tipo do bloco *</label>
                                    <select
                                        x-model="etapa.tipo_bloco"
                                        :name="'etapas[' + etapaIndex + '][tipo_bloco]'"
                                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                                        required
                                    >
                                        <template x-for="tipo in tiposBlocoDisponiveis" :key="tipo.id">
                                            <option :value="tipo.id" x-text="tipo.label"></option>
                                        </template>
                                    </select>
                                    <template x-if="hasError(`etapas.${etapaIndex}.tipo_bloco`)">
                                        <p class="mt-1 text-xs text-rose-300" x-text="errorText(`etapas.${etapaIndex}.tipo_bloco`)"></p>
                                    </template>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm text-slate-300">Subtítulo</label>
                                    <input
                                        type="text"
                                        x-model="etapa.subtitulo"
                                        :name="'etapas[' + etapaIndex + '][subtitulo]'"
                                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                                        placeholder="Ex.: início leve na orla, pausa para almoço..."
                                    >
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm text-slate-300">Texto da etapa</label>
                                    <textarea
                                        rows="4"
                                        x-model="etapa.descricao"
                                        :name="'etapas[' + etapaIndex + '][descricao]'"
                                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                                        placeholder="Descreva o que o visitante fará nessa etapa e por que ela é especial."
                                    ></textarea>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-100">Pontos da etapa</h3>
                                        <p class="mt-1 text-xs text-slate-400">
                                            Escolha os pontos turísticos e a ordem em que aparecem nessa etapa.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        @click="addPoint(etapaIndex)"
                                        class="rounded-xl bg-white/10 px-3 py-2 text-xs font-medium text-slate-100 hover:bg-white/15"
                                    >
                                        + Adicionar ponto
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    <template x-for="(ponto, pontoIndex) in etapa.pontos" :key="ponto._key">
                                        <div class="rounded-xl border border-white/10 bg-white/[0.03] p-3">
                                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                                <div class="text-sm font-medium text-slate-200">
                                                    Ponto <span x-text="pontoIndex + 1"></span>
                                                </div>

                                                <div class="flex flex-wrap gap-2">
                                                    <button
                                                        type="button"
                                                        @click="movePointUp(etapaIndex, pontoIndex)"
                                                        class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
                                                    >
                                                        Subir
                                                    </button>

                                                    <button
                                                        type="button"
                                                        @click="movePointDown(etapaIndex, pontoIndex)"
                                                        class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
                                                    >
                                                        Descer
                                                    </button>

                                                    <button
                                                        type="button"
                                                        @click="removePoint(etapaIndex, pontoIndex)"
                                                        class="rounded-lg border border-rose-500/20 bg-rose-500/10 px-3 py-1.5 text-xs text-rose-200 hover:bg-rose-500/15"
                                                    >
                                                        Remover
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="grid gap-3 md:grid-cols-2">
                                                <div class="md:col-span-2">
                                                    <label class="mb-1 block text-sm text-slate-300">Ponto turístico *</label>
                                                    <select
                                                        x-model="ponto.ponto_turistico_id"
                                                        :name="'etapas[' + etapaIndex + '][pontos][' + pontoIndex + '][ponto_turistico_id]'"
                                                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                                                        required
                                                    >
                                                        <option value="">Selecione</option>
                                                        <template x-for="item in pontosDisponiveis" :key="item.id">
                                                            <option :value="String(item.id)" x-text="item.nome"></option>
                                                        </template>
                                                    </select>
                                                    <template x-if="hasError(`etapas.${etapaIndex}.pontos.${pontoIndex}.ponto_turistico_id`)">
                                                        <p class="mt-1 text-xs text-rose-300" x-text="errorText(`etapas.${etapaIndex}.pontos.${pontoIndex}.ponto_turistico_id`)"></p>
                                                    </template>
                                                </div>

                                                <div>
                                                    <label class="mb-1 block text-sm text-slate-300">Tempo estimado (min)</label>
                                                    <input
                                                        type="number"
                                                        min="5"
                                                        max="1440"
                                                        x-model="ponto.tempo_estimado_min"
                                                        :name="'etapas[' + etapaIndex + '][pontos][' + pontoIndex + '][tempo_estimado_min]'"
                                                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                                                        placeholder="60"
                                                    >
                                                </div>

                                                <div>
                                                    <label class="mb-1 block text-sm text-slate-300">Destaque</label>
                                                    <div class="flex h-[46px] items-center rounded-xl border border-white/10 bg-white/5 px-3">
                                                        <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                                                            <input
                                                                type="checkbox"
                                                                x-model="ponto.destaque"
                                                                :name="'etapas[' + etapaIndex + '][pontos][' + pontoIndex + '][destaque]'"
                                                                value="1"
                                                                class="rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-500"
                                                            >
                                                            Marcar como destaque
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="md:col-span-2">
                                                    <label class="mb-1 block text-sm text-slate-300">Observação curta</label>
                                                    <input
                                                        type="text"
                                                        x-model="ponto.observacao_curta"
                                                        :name="'etapas[' + etapaIndex + '][pontos][' + pontoIndex + '][observacao_curta]'"
                                                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                                                        placeholder="Ex.: ideal para fotos, parada rápida, melhor no fim da tarde..."
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-[#0F1412] p-5">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-xs uppercase tracking-[0.18em] text-emerald-300/80">Empresas sugeridas</div>
                    <h2 class="mt-1 text-lg font-semibold text-slate-100">Curadoria do roteiro</h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Selecione só as empresas que realmente ajudam o visitante nesse percurso.
                    </p>
                </div>

                <button
                    type="button"
                    @click="addEmpresa()"
                    class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
                >
                    + Adicionar empresa
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(empresa, empresaIndex) in empresasEscolhidas" :key="empresa._key">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div class="text-sm font-semibold text-slate-100">
                                Sugestão <span x-text="empresaIndex + 1"></span>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    @click="moveEmpresaUp(empresaIndex)"
                                    class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
                                >
                                    Subir
                                </button>

                                <button
                                    type="button"
                                    @click="moveEmpresaDown(empresaIndex)"
                                    class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
                                >
                                    Descer
                                </button>

                                <button
                                    type="button"
                                    @click="removeEmpresa(empresaIndex)"
                                    class="rounded-lg border border-rose-500/20 bg-rose-500/10 px-3 py-1.5 text-xs text-rose-200 hover:bg-rose-500/15"
                                >
                                    Remover
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-sm text-slate-300">Empresa *</label>
                                <select
                                    x-model="empresa.empresa_id"
                                    :name="'empresas[' + empresaIndex + '][empresa_id]'"
                                    class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                                    required
                                >
                                    <option value="">Selecione</option>
                                    <template x-for="item in empresasDisponiveis" :key="item.id">
                                        <option :value="String(item.id)" x-text="item.nome"></option>
                                    </template>
                                </select>
                                <template x-if="hasError(`empresas.${empresaIndex}.empresa_id`)">
                                    <p class="mt-1 text-xs text-rose-300" x-text="errorText(`empresas.${empresaIndex}.empresa_id`)"></p>
                                </template>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm text-slate-300">Tipo da sugestão *</label>
                                <select
                                    x-model="empresa.tipo_sugestao"
                                    :name="'empresas[' + empresaIndex + '][tipo_sugestao]'"
                                    class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                                    required
                                >
                                    <template x-for="tipo in tiposSugestaoDisponiveis" :key="tipo.id">
                                        <option :value="tipo.id" x-text="tipo.label"></option>
                                    </template>
                                </select>
                                <template x-if="hasError(`empresas.${empresaIndex}.tipo_sugestao`)">
                                    <p class="mt-1 text-xs text-rose-300" x-text="errorText(`empresas.${empresaIndex}.tipo_sugestao`)"></p>
                                </template>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm text-slate-300">Destaque</label>
                                <div class="flex h-[46px] items-center rounded-xl border border-white/10 bg-white/5 px-3">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                                        <input
                                            type="checkbox"
                                            x-model="empresa.destaque"
                                            :name="'empresas[' + empresaIndex + '][destaque]'"
                                            value="1"
                                            class="rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-500"
                                        >
                                        Destacar no roteiro
                                    </label>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-1 block text-sm text-slate-300">Observação curta</label>
                                <input
                                    type="text"
                                    x-model="empresa.observacao_curta"
                                    :name="'empresas[' + empresaIndex + '][observacao_curta]'"
                                    class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                                    placeholder="Ex.: passeio guiado, boa opção para almoço, apoio de receptivo..."
                                >
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </section>
    </div>

    <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
        <section class="rounded-2xl border border-white/10 bg-[#0F1412] p-5">
            <div class="text-xs uppercase tracking-[0.18em] text-emerald-300/80">Preview</div>
            <h2 class="mt-1 text-lg font-semibold text-slate-100">Publicação</h2>

            <div class="mt-4 space-y-2">
                <template x-for="item in checklist()" :key="item.label">
                    <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2.5">
                        <div
                            class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full text-[11px] font-bold"
                            :class="item.ok ? 'bg-emerald-500/15 text-emerald-300' : 'bg-rose-500/15 text-rose-300'"
                            x-text="item.ok ? '✓' : '!'"
                        ></div>

                        <div class="min-w-0">
                            <div class="text-sm font-medium text-slate-100" x-text="item.label"></div>
                            <div class="text-xs text-slate-400" x-text="item.desc"></div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-4 rounded-xl border border-white/10 bg-slate-950/40 p-3 text-sm">
                <div class="text-slate-400">Status atual</div>
                <div class="mt-1 font-semibold text-slate-100" x-text="statusLabel()"></div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-white/10 bg-[#0F1412]">
            <div class="relative h-48 w-full bg-slate-800">
                <template x-if="coverPreview">
                    <img :src="coverPreview" alt="Prévia" class="h-full w-full object-cover">
                </template>

                <template x-if="!coverPreview">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-700/30 via-slate-800 to-slate-950"></div>
                </template>

                <div class="absolute inset-x-0 bottom-0 p-4">
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full border border-white/15 bg-black/40 px-2.5 py-1 text-xs text-slate-100" x-text="duracaoLabel()"></span>
                        <span class="rounded-full border border-white/15 bg-black/40 px-2.5 py-1 text-xs text-slate-100" x-text="perfilLabel()"></span>
                    </div>
                </div>
            </div>

            <div class="space-y-4 p-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-100" x-text="titulo || 'Título do roteiro'"></h3>
                    <p class="mt-2 text-sm leading-6 text-slate-300" x-text="resumo || 'O resumo do roteiro aparecerá aqui para prévia.'"></p>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">Etapas</div>
                        <div class="mt-1 text-sm font-semibold text-slate-100" x-text="etapas.length"></div>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">Empresas</div>
                        <div class="mt-1 text-sm font-semibold text-slate-100" x-text="empresasEscolhidas.length"></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 text-xs uppercase tracking-wide text-slate-500">Estrutura</div>
                    <div class="space-y-2">
                        <template x-for="(etapa, idx) in etapas.slice(0, 4)" :key="etapa._key">
                            <div class="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2">
                                <div class="text-sm font-medium text-slate-100">
                                    <span x-text="idx + 1"></span>.
                                    <span x-text="etapa.titulo || 'Sem título'"></span>
                                </div>
                                <div class="mt-1 text-xs text-slate-400">
                                    <span x-text="etapa.pontos.length"></span> ponto(s)
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <template x-if="empresasEscolhidas.length">
                    <div>
                        <div class="mb-2 text-xs uppercase tracking-wide text-slate-500">Empresas sugeridas</div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="empresa in empresasEscolhidas.slice(0, 6)" :key="empresa._key">
                                <span class="rounded-full border border-white/10 bg-white/[0.03] px-2.5 py-1 text-xs text-slate-200" x-text="empresaNome(empresa.empresa_id)"></span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </section>
    </aside>

    <script>
        function roteiroBuilder(config) {
            return {
                titulo: @json(old('titulo', $roteiro->titulo ?? '')),
                slug: @json(old('slug', $roteiro->slug ?? '')),
                resumo: @json(old('resumo', $roteiro->resumo ?? '')),
                status: @json($statusAtual),
                duracao: @json(old('duracao_slug', $roteiro->duracao_slug ?? '1_dia')),
                perfil: @json(old('perfil_slug', $roteiro->perfil_slug ?? 'geral')),

                etapas: [],
                empresasEscolhidas: [],

                pontosDisponiveis: config.pontosDisponiveis || [],
                empresasDisponiveis: config.empresasDisponiveis || [],
                tiposBlocoDisponiveis: config.tiposBlocoDisponiveis || [],
                tiposSugestaoDisponiveis: config.tiposSugestaoDisponiveis || [],

                coverPreview: config.capaAtual || null,
                originalCover: config.capaAtual || null,
                removeCurrentCover: false,

                errors: @json($errors->toArray()),

                init() {
                    this.etapas = (config.etapasInicial || []).map((etapa) => this.normalizeEtapa(etapa));
                    this.empresasEscolhidas = (config.empresasInicial || []).map((empresa) => this.normalizeEmpresa(empresa));

                    if (!this.etapas.length) {
                        this.etapas = [this.blankEtapa()];
                    }
                },

                uid() {
                    return Math.random().toString(36).slice(2) + Date.now().toString(36);
                },

                normalizeEtapa(etapa = {}) {
                    return {
                        _key: this.uid(),
                        titulo: etapa.titulo ?? '',
                        subtitulo: etapa.subtitulo ?? '',
                        descricao: etapa.descricao ?? '',
                        tipo_bloco: etapa.tipo_bloco ?? 'extra',
                        pontos: Array.isArray(etapa.pontos) && etapa.pontos.length
                            ? etapa.pontos.map((ponto) => this.normalizePoint(ponto))
                            : [this.blankPoint()],
                    };
                },

                normalizePoint(ponto = {}) {
                    return {
                        _key: this.uid(),
                        ponto_turistico_id: ponto.ponto_turistico_id != null ? String(ponto.ponto_turistico_id) : '',
                        observacao_curta: ponto.observacao_curta ?? '',
                        tempo_estimado_min: ponto.tempo_estimado_min ?? '',
                        destaque: Boolean(ponto.destaque),
                    };
                },

                normalizeEmpresa(empresa = {}) {
                    return {
                        _key: this.uid(),
                        empresa_id: empresa.empresa_id != null ? String(empresa.empresa_id) : '',
                        tipo_sugestao: empresa.tipo_sugestao ?? 'apoio',
                        observacao_curta: empresa.observacao_curta ?? '',
                        destaque: Boolean(empresa.destaque),
                    };
                },

                blankEtapa() {
                    return {
                        _key: this.uid(),
                        titulo: '',
                        subtitulo: '',
                        descricao: '',
                        tipo_bloco: 'extra',
                        pontos: [this.blankPoint()],
                    };
                },

                blankPoint() {
                    return {
                        _key: this.uid(),
                        ponto_turistico_id: '',
                        observacao_curta: '',
                        tempo_estimado_min: '',
                        destaque: false,
                    };
                },

                blankEmpresa() {
                    return {
                        _key: this.uid(),
                        empresa_id: '',
                        tipo_sugestao: 'apoio',
                        observacao_curta: '',
                        destaque: false,
                    };
                },

                addEtapa() {
                    this.etapas.push(this.blankEtapa());
                },

                removeEtapa(index) {
                    if (this.etapas.length === 1) {
                        this.etapas = [this.blankEtapa()];
                        return;
                    }
                    this.etapas.splice(index, 1);
                },

                moveEtapaUp(index) {
                    if (index === 0) return;
                    [this.etapas[index - 1], this.etapas[index]] = [this.etapas[index], this.etapas[index - 1]];
                },

                moveEtapaDown(index) {
                    if (index >= this.etapas.length - 1) return;
                    [this.etapas[index + 1], this.etapas[index]] = [this.etapas[index], this.etapas[index + 1]];
                },

                addPoint(etapaIndex) {
                    this.etapas[etapaIndex].pontos.push(this.blankPoint());
                },

                removePoint(etapaIndex, pontoIndex) {
                    const pontos = this.etapas[etapaIndex].pontos;
                    if (pontos.length === 1) {
                        pontos.splice(0, 1, this.blankPoint());
                        return;
                    }
                    pontos.splice(pontoIndex, 1);
                },

                movePointUp(etapaIndex, pontoIndex) {
                    if (pontoIndex === 0) return;
                    const pontos = this.etapas[etapaIndex].pontos;
                    [pontos[pontoIndex - 1], pontos[pontoIndex]] = [pontos[pontoIndex], pontos[pontoIndex - 1]];
                },

                movePointDown(etapaIndex, pontoIndex) {
                    const pontos = this.etapas[etapaIndex].pontos;
                    if (pontoIndex >= pontos.length - 1) return;
                    [pontos[pontoIndex + 1], pontos[pontoIndex]] = [pontos[pontoIndex], pontos[pontoIndex + 1]];
                },

                addEmpresa() {
                    this.empresasEscolhidas.push(this.blankEmpresa());
                },

                removeEmpresa(index) {
                    this.empresasEscolhidas.splice(index, 1);
                },

                moveEmpresaUp(index) {
                    if (index === 0) return;
                    [this.empresasEscolhidas[index - 1], this.empresasEscolhidas[index]] = [this.empresasEscolhidas[index], this.empresasEscolhidas[index - 1]];
                },

                moveEmpresaDown(index) {
                    if (index >= this.empresasEscolhidas.length - 1) return;
                    [this.empresasEscolhidas[index + 1], this.empresasEscolhidas[index]] = [this.empresasEscolhidas[index], this.empresasEscolhidas[index + 1]];
                },

                updateCoverPreview(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file) {
                        this.coverPreview = this.removeCurrentCover ? null : this.originalCover;
                        return;
                    }

                    this.removeCurrentCover = false;
                    this.coverPreview = URL.createObjectURL(file);
                },

                syncSlug() {
                    if (!this.slug || this.slug.trim() === '' || this.slug === this.slugify(this.slug)) {
                        this.slug = this.slugify(this.titulo);
                    }
                },

                slugify(value) {
                    return (value || '')
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .toLowerCase()
                        .trim()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                },

                totalPontos() {
                    return this.etapas.reduce((acc, etapa) => acc + (etapa.pontos?.length || 0), 0);
                },

                hasCoverReady() {
                    if (this.removeCurrentCover) {
                        return !!this.coverPreview;
                    }
                    return !!this.coverPreview || !!this.originalCover;
                },

                checklist() {
                    return [
                        {
                            label: 'Título e resumo',
                            desc: 'O roteiro precisa ter identidade e contexto claros.',
                            ok: !!this.titulo && !!this.resumo,
                        },
                        {
                            label: 'Capa',
                            desc: 'A capa fortalece o card público e a página do roteiro.',
                            ok: this.hasCoverReady(),
                        },
                        {
                            label: 'Etapas',
                            desc: 'É preciso ter pelo menos uma etapa estruturada.',
                            ok: this.etapas.length > 0,
                        },
                        {
                            label: 'Pontos vinculados',
                            desc: 'Cada etapa deve conduzir o visitante por lugares reais.',
                            ok: this.totalPontos() > 0,
                        },
                    ];
                },

                statusLabel() {
                    if (this.status === 'publicado') return 'Publicado';
                    if (this.status === 'arquivado') return 'Arquivado';
                    return 'Rascunho';
                },

                duracaoLabel() {
                    const item = {
                        @foreach(($duracoes ?? []) as $key => $label)
                            '{{ $key }}': '{{ $label }}',
                        @endforeach
                    };
                    return item[this.duracao] || 'Sem duração';
                },

                perfilLabel() {
                    const item = {
                        @foreach(($perfis ?? []) as $key => $label)
                            '{{ $key }}': '{{ $label }}',
                        @endforeach
                    };
                    return item[this.perfil] || 'Sem perfil';
                },

                pontoNome(id) {
                    const item = this.pontosDisponiveis.find((p) => String(p.id) === String(id));
                    return item ? item.nome : 'Ponto não selecionado';
                },

                empresaNome(id) {
                    const item = this.empresasDisponiveis.find((e) => String(e.id) === String(id));
                    return item ? item.nome : 'Empresa não selecionada';
                },

                hasError(field) {
                    return !!this.errors[field];
                },

                errorText(field) {
                    return this.errors[field] ? this.errors[field][0] : '';
                }
            }
        }
    </script>
</div>

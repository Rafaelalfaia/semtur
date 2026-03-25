@php
    $empresasForm = old('empresas');

    if ($empresasForm === null) {
        $empresasForm = collect($pagina->empresasSelecionadas ?? [])->map(function ($item) {
            return [
                'empresa_id' => $item->empresa_id,
                'observacao_curta' => $item->observacao_curta,
                'destaque' => (bool) $item->destaque,
            ];
        })->values()->all();
    }

    $empresasOptions = collect($empresas ?? [])->map(fn ($empresa) => [
        'id' => (int) $empresa->id,
        'nome' => $empresa->nome,
        'cidade' => $empresa->cidade,
    ])->values()->all();
@endphp

@push('head')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

<div
    x-data="ondeComerBuilder({
        empresasInicial: {{ \Illuminate\Support\Js::from($empresasForm) }},
        empresasDisponiveis: {{ \Illuminate\Support\Js::from($empresasOptions) }},
        heroAtual: {{ \Illuminate\Support\Js::from($pagina->hero_url ?? null) }},
    })"
    class="grid gap-6 xl:grid-cols-3"
>
    <div class="space-y-6 xl:col-span-2">
        <section class="rounded-2xl border border-white/10 bg-[#0F1412] p-5">
            <div class="mb-5">
                <div class="text-xs uppercase tracking-[0.18em] text-emerald-300/80">Dados gerais</div>
                <h2 class="mt-1 text-lg font-semibold text-slate-100">Identidade da página</h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">Título *</label>
                    <input
                        type="text"
                        name="titulo"
                        x-model="titulo"
                        value="{{ old('titulo', $pagina->titulo ?? 'Onde comer em Altamira') }}"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        required
                    >
                    @error('titulo')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">Subtítulo</label>
                    <input
                        type="text"
                        name="subtitulo"
                        value="{{ old('subtitulo', $pagina->subtitulo ?? '') }}"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        placeholder="Sabores locais, culinária regional, experiências gastronômicas..."
                    >
                    @error('subtitulo')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm text-slate-300">Status *</label>
                    <select
                        name="status"
                        x-model="status"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        required
                    >
                        <option value="rascunho" @selected(old('status', $pagina->status ?? 'rascunho') === 'rascunho')>Rascunho</option>
                        <option value="publicado" @selected(old('status', $pagina->status ?? 'rascunho') === 'publicado')>Publicado</option>
                        <option value="arquivado" @selected(old('status', $pagina->status ?? 'rascunho') === 'arquivado')>Arquivado</option>
                    </select>
                    @error('status')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">Resumo *</label>
                    <textarea
                        name="resumo"
                        rows="3"
                        x-model="resumo"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        required
                    >{{ old('resumo', $pagina->resumo ?? '') }}</textarea>
                    @error('resumo')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">Texto de introdução</label>
                    <textarea
                        name="texto_intro"
                        rows="5"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                    >{{ old('texto_intro', $pagina->texto_intro ?? '') }}</textarea>
                    @error('texto_intro')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">Texto da gastronomia local</label>
                    <textarea
                        name="texto_gastronomia_local"
                        rows="7"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        placeholder="Explique o que torna a gastronomia de Altamira especial, sabores, ingredientes, identidade local..."
                    >{{ old('texto_gastronomia_local', $pagina->texto_gastronomia_local ?? '') }}</textarea>
                    @error('texto_gastronomia_local')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">Texto de dicas</label>
                    <textarea
                        name="texto_dicas"
                        rows="5"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        placeholder="Melhores horários, o que provar, dicas de experiência..."
                    >{{ old('texto_dicas', $pagina->texto_dicas ?? '') }}</textarea>
                    @error('texto_dicas')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-[#0F1412] p-5">
            <div class="mb-5">
                <div class="text-xs uppercase tracking-[0.18em] text-emerald-300/80">Hero e SEO</div>
                <h2 class="mt-1 text-lg font-semibold text-slate-100">Imagem principal e metadados</h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">Imagem principal</label>
                    <input
                        type="file"
                        name="hero"
                        accept="image/*"
                        @change="updateHeroPreview($event)"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                    >
                    @error('hero')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror

                    <div class="mt-4 overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60">
                        <div class="relative h-56 w-full bg-slate-800">
                            <template x-if="heroPreview">
                                <img :src="heroPreview" alt="Prévia" class="h-full w-full object-cover">
                            </template>

                            <template x-if="!heroPreview">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-700/30 via-slate-800 to-slate-950"></div>
                            </template>
                        </div>
                    </div>

                    @if($pagina->hero_url)
                        <div class="mt-3">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                                <input type="hidden" name="remover_hero" value="0">
                                <input
                                    type="checkbox"
                                    name="remover_hero"
                                    value="1"
                                    x-model="removeCurrentHero"
                                    class="rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-500"
                                >
                                Remover imagem atual
                            </label>
                        </div>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">SEO title</label>
                    <input
                        type="text"
                        name="seo_title"
                        value="{{ old('seo_title', $pagina->seo_title ?? '') }}"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                    >
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm text-slate-300">SEO description</label>
                    <textarea
                        name="seo_description"
                        rows="3"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                    >{{ old('seo_description', $pagina->seo_description ?? '') }}</textarea>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-[#0F1412] p-5">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-xs uppercase tracking-[0.18em] text-emerald-300/80">Empresas</div>
                    <h2 class="mt-1 text-lg font-semibold text-slate-100">Seleção manual da gastronomia</h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Escolha apenas as empresas que devem aparecer em “Onde comer”.
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

            @error('empresas')<p class="mb-3 text-xs text-rose-300">{{ $message }}</p>@enderror

            <div class="space-y-3">
                <template x-for="(empresa, index) in empresasEscolhidas" :key="empresa._key">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div class="text-sm font-semibold text-slate-100">
                                Empresa <span x-text="index + 1"></span>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    @click="moveEmpresaUp(index)"
                                    class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
                                >
                                    Subir
                                </button>

                                <button
                                    type="button"
                                    @click="moveEmpresaDown(index)"
                                    class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
                                >
                                    Descer
                                </button>

                                <button
                                    type="button"
                                    @click="removeEmpresa(index)"
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
                                    :name="'empresas[' + index + '][empresa_id]'"
                                    class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                                    required
                                >
                                    <option value="">Selecione</option>
                                    <template x-for="item in empresasDisponiveis" :key="item.id">
                                        <option :value="String(item.id)" x-text="item.nome + (item.cidade ? ' • ' + item.cidade : '')"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-1 block text-sm text-slate-300">Observação curta</label>
                                <input
                                    type="text"
                                    x-model="empresa.observacao_curta"
                                    :name="'empresas[' + index + '][observacao_curta]'"
                                    class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                                    placeholder="Ex.: bom para almoço regional, ideal para jantar, café especial..."
                                >
                            </div>

                            <div>
                                <label class="mb-1 block text-sm text-slate-300">Destaque</label>
                                <div class="flex h-[46px] items-center rounded-xl border border-white/10 bg-white/5 px-3">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                                        <input
                                            type="checkbox"
                                            x-model="empresa.destaque"
                                            :name="'empresas[' + index + '][destaque]'"
                                            value="1"
                                            class="rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-500"
                                        >
                                        Destacar na página
                                    </label>
                                </div>
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
            <h2 class="mt-1 text-lg font-semibold text-slate-100">Resumo da publicação</h2>

            <div class="mt-4 space-y-3">
                <div class="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-3">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500">Status</div>
                    <div class="mt-1 text-sm font-semibold text-slate-100" x-text="statusLabel()"></div>
                </div>

                <div class="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-3">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500">Empresas selecionadas</div>
                    <div class="mt-1 text-sm font-semibold text-slate-100" x-text="empresasEscolhidas.length"></div>
                </div>
            </div>

            <div class="mt-5 overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60">
                <div class="relative h-48 bg-slate-800">
                    <template x-if="heroPreview">
                        <img :src="heroPreview" alt="Prévia" class="h-full w-full object-cover">
                    </template>

                    <template x-if="!heroPreview">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-700/30 via-slate-800 to-slate-950"></div>
                    </template>
                </div>

                <div class="p-4">
                    <div class="text-xs uppercase tracking-[0.16em] text-emerald-300/80">Onde comer</div>
                    <h3 class="mt-2 text-lg font-semibold text-slate-100" x-text="titulo || 'Onde comer em Altamira'"></h3>
                    <p class="mt-2 text-sm leading-7 text-slate-300" x-text="resumo || 'O resumo da página aparecerá aqui.'"></p>
                </div>
            </div>
        </section>
    </aside>

    <script>
        function ondeComerBuilder(config) {
            return {
                titulo: @json(old('titulo', $pagina->titulo ?? 'Onde comer em Altamira')),
                resumo: @json(old('resumo', $pagina->resumo ?? '')),
                status: @json(old('status', $pagina->status ?? 'rascunho')),

                empresasEscolhidas: [],
                empresasDisponiveis: config.empresasDisponiveis || [],

                heroPreview: config.heroAtual || null,
                originalHero: config.heroAtual || null,
                removeCurrentHero: false,

                init() {
                    this.empresasEscolhidas = (config.empresasInicial || []).map((item) => this.normalizeEmpresa(item));
                },

                uid() {
                    return Math.random().toString(36).slice(2) + Date.now().toString(36);
                },

                normalizeEmpresa(item = {}) {
                    return {
                        _key: this.uid(),
                        empresa_id: item.empresa_id != null ? String(item.empresa_id) : '',
                        observacao_curta: item.observacao_curta ?? '',
                        destaque: Boolean(item.destaque),
                    };
                },

                blankEmpresa() {
                    return {
                        _key: this.uid(),
                        empresa_id: '',
                        observacao_curta: '',
                        destaque: false,
                    };
                },

                addEmpresa() {
                    this.empresasEscolhidas.push(this.blankEmpresa());
                },

                removeEmpresa(index) {
                    this.empresasEscolhidas.splice(index, 1);
                },

                moveEmpresaUp(index) {
                    if (index === 0) return;
                    [this.empresasEscolhidas[index - 1], this.empresasEscolhidas[index]] =
                        [this.empresasEscolhidas[index], this.empresasEscolhidas[index - 1]];
                },

                moveEmpresaDown(index) {
                    if (index >= this.empresasEscolhidas.length - 1) return;
                    [this.empresasEscolhidas[index + 1], this.empresasEscolhidas[index]] =
                        [this.empresasEscolhidas[index], this.empresasEscolhidas[index + 1]];
                },

                updateHeroPreview(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file) {
                        this.heroPreview = this.removeCurrentHero ? null : this.originalHero;
                        return;
                    }

                    this.removeCurrentHero = false;
                    this.heroPreview = URL.createObjectURL(file);
                },

                statusLabel() {
                    if (this.status === 'publicado') return 'Publicado';
                    if (this.status === 'arquivado') return 'Arquivado';
                    return 'Rascunho';
                }
            }
        }
    </script>
</div>

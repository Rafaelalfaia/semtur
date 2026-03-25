@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $coverCurrent = old('remover_capa') ? null : ($guia->capa_url ?? null);
@endphp

<div
    x-data="{
        coverPreview: @js($coverCurrent),
        removeCurrentCover: @js((bool) old('remover_capa')),
        linkAcesso: @js(old('link_acesso', $guia->link_acesso ?? '')),

        updateCoverPreview(event) {
            const file = event.target.files?.[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = e => {
                this.coverPreview = e.target.result;
                this.removeCurrentCover = false;
            };
            reader.readAsDataURL(file);
        },

        buildEmbedUrl(url) {
            url = String(url || '').trim();
            if (!url) return '';

            let match = url.match(/docs\\.google\\.com\\/(document|spreadsheets|presentation)\\/d\\/([a-zA-Z0-9_-]+)/);
            if (match) {
                return `https://docs.google.com/${match[1]}/d/${match[2]}/preview`;
            }

            match = url.match(/drive\\.google\\.com\\/file\\/d\\/([a-zA-Z0-9_-]+)/);
            if (match) {
                return `https://drive.google.com/file/d/${match[1]}/preview`;
            }

            try {
                const parsed = new URL(url);
                const id = parsed.searchParams.get('id');
                if (id) {
                    return `https://drive.google.com/file/d/${id}/preview`;
                }
            } catch (e) {}

            return url;
        }
    }"
    class="ui-guide-form space-y-6"
>
    @if($errors->any())
        <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-4 text-sm text-rose-200">
            <div class="font-semibold">Revise os campos abaixo:</div>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-rose-100/90">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <section class="rounded-2xl border border-white/10 bg-[#0F1412] p-5">
                <div class="mb-5">
                    <div class="text-xs uppercase tracking-[0.18em] text-emerald-300/80">Conteúdo</div>
                    <h2 class="mt-1 text-lg font-semibold text-slate-100">Dados principais</h2>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm text-slate-300">Tipo</label>
                        <select
                            name="tipo"
                            class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                            required
                        >
                            @foreach(($tipos ?? []) as $tipoKey => $tipoLabel)
                                <option value="{{ $tipoKey }}" @selected(old('tipo', $guia->tipo ?? '') === $tipoKey)>
                                    {{ $tipoLabel }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm text-slate-300">Ordem</label>
                        <input
                            type="number"
                            name="ordem"
                            min="0"
                            value="{{ old('ordem', $guia->ordem ?? 0) }}"
                            class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        >
                        @error('ordem')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm text-slate-300">Nome</label>
                        <input
                            type="text"
                            name="nome"
                            value="{{ old('nome', $guia->nome ?? '') }}"
                            class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                            placeholder="Ex.: Guia do visitante de Altamira"
                            required
                        >
                        @error('nome')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm text-slate-300">Slug</label>
                        <input
                            type="text"
                            name="slug"
                            value="{{ old('slug', $guia->slug ?? '') }}"
                            class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                            placeholder="deixe vazio para gerar automaticamente"
                        >
                        @error('slug')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm text-slate-300">Descrição</label>
                        <textarea
                            name="descricao"
                            rows="7"
                            class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                            placeholder="Descreva o material, o público, o objetivo e o que o visitante vai encontrar."
                            required
                        >{{ old('descricao', $guia->descricao ?? '') }}</textarea>
                        @error('descricao')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-white/10 bg-[#0F1412] p-5">
                <div class="mb-5">
                    <div class="text-xs uppercase tracking-[0.18em] text-emerald-300/80">Acesso</div>
                    <h2 class="mt-1 text-lg font-semibold text-slate-100">Link do Google Drive</h2>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm text-slate-300">Link de acesso</label>
                        <input
                            type="url"
                            name="link_acesso"
                            x-model="linkAcesso"
                            value="{{ old('link_acesso', $guia->link_acesso ?? '') }}"
                            class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                            placeholder="https://drive.google.com/..."
                            required
                        >
                        @error('link_acesso')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">Preview interno</div>
                        <p class="mt-2 text-sm leading-6 text-slate-300">
                            No site público, o material será aberto dentro do projeto usando um visualizador embutido.
                        </p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <a
                                :href="buildEmbedUrl(linkAcesso)"
                                x-show="linkAcesso"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                            >
                                Testar preview
                            </a>

                            <a
                                :href="linkAcesso"
                                x-show="linkAcesso"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10"
                            >
                                Abrir link original
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-2xl border border-white/10 bg-[#0F1412] p-5">
                <div class="mb-5">
                    <div class="text-xs uppercase tracking-[0.18em] text-emerald-300/80">Publicação</div>
                    <h2 class="mt-1 text-lg font-semibold text-slate-100">Status do material</h2>
                </div>

                <div>
                    <label class="mb-1 block text-sm text-slate-300">Status</label>
                    <select
                        name="status"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                        required
                    >
                        @foreach(($statuses ?? []) as $itemStatus)
                            <option value="{{ $itemStatus }}" @selected(old('status', $guia->status ?? 'rascunho') === $itemStatus)>
                                {{ ucfirst($itemStatus) }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="mt-4 rounded-2xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                    <div class="font-semibold">Regra importante</div>
                    <p class="mt-1 leading-6">
                        Para publicar, o material precisa ter uma capa válida e um link do Google Drive, Docs, Sheets ou Slides.
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border border-white/10 bg-[#0F1412] p-5">
                <div class="mb-5">
                    <div class="text-xs uppercase tracking-[0.18em] text-emerald-300/80">Imagem</div>
                    <h2 class="mt-1 text-lg font-semibold text-slate-100">Capa do material</h2>
                </div>

                <div>
                    <label class="mb-1 block text-sm text-slate-300">Imagem de capa</label>
                    <input
                        type="file"
                        name="capa"
                        accept="image/*"
                        @change="updateCoverPreview($event)"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-slate-100"
                    >
                    @error('capa')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror

                    <div class="mt-4 overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60">
                        <div class="relative h-56 w-full bg-slate-800">
                            <template x-if="coverPreview && !removeCurrentCover">
                                <img :src="coverPreview" alt="Prévia da capa" class="h-full w-full object-cover">
                            </template>

                            <template x-if="!coverPreview || removeCurrentCover">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-700/30 via-slate-800 to-slate-950"></div>
                            </template>
                        </div>

                        <div class="p-3 text-xs text-slate-400">
                            A capa será usada na listagem pública e na página individual do material.
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
            </section>
        </div>
    </section>
</div>

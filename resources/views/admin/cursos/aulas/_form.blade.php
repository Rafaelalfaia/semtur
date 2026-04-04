@php
    $aula ??= null;
    $isEdit = ($mode ?? 'create') === 'edit';
    $coverCurrent = old('remover_capa') ? null : ($aula->capa_url ?? null);
@endphp

<div
    x-data="{
        coverPreview: @js($coverCurrent),
        removeCurrentCover: @js((bool) old('remover_capa')),
        linkAcesso: @js(old('link_acesso', $aula->link_acesso ?? '')),
        buildEmbedUrl(url) {
            url = String(url || '').trim();
            if (!url) return '';

            let match = url.match(/(?:drive|docs)\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/);
            if (match) return `https://drive.google.com/file/d/${match[1]}/preview`;

            try {
                const parsed = new URL(url);
                const id = parsed.searchParams.get('id');
                if (id) return `https://drive.google.com/file/d/${id}/preview`;
            } catch (e) {}

            return url;
        }
    }"
    class="space-y-4"
>
    <x-dashboard.section-card title="Dados principais" subtitle="Cadastre a aula com nome, descrição e o link do vídeo no Google Drive.">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="ui-form-label" for="nome">Nome</label>
                <input id="nome" name="nome" type="text" value="{{ old('nome', $aula->nome ?? '') }}" class="ui-form-control" placeholder="Ex.: Boas-vindas e visão geral" required>
                @error('nome')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="ui-form-label" for="slug">Slug</label>
                <input id="slug" name="slug" type="text" value="{{ old('slug', $aula->slug ?? '') }}" class="ui-form-control" placeholder="deixe vazio para gerar automaticamente">
                @error('slug')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="ui-form-label" for="ordem">Ordem</label>
                <input id="ordem" name="ordem" type="number" min="0" value="{{ old('ordem', $aula->ordem ?? 0) }}" class="ui-form-control">
                @error('ordem')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="ui-form-label" for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" rows="6" class="ui-form-control" placeholder="Descreva o conteúdo da aula e o objetivo desta etapa.">{{ old('descricao', $aula->descricao ?? '') }}</textarea>
                @error('descricao')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Vídeo" subtitle="A aula usa um link do Google Drive para abrir ou pré-visualizar o conteúdo.">
        <div class="space-y-4">
            <div>
                <label class="ui-form-label" for="link_acesso">Link do Google Drive</label>
                <input id="link_acesso" name="link_acesso" type="url" x-model="linkAcesso" value="{{ old('link_acesso', $aula->link_acesso ?? '') }}" class="ui-form-control" placeholder="https://drive.google.com/..." required>
                @error('link_acesso')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>

            <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-input-bg)] p-4">
                <div class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Preview interno</div>
                <p class="mt-2 text-sm text-[var(--ui-text-soft)]">Use os atalhos abaixo para validar se o link está acessível antes de publicar a aula.</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a :href="buildEmbedUrl(linkAcesso)" x-show="linkAcesso" target="_blank" rel="noopener noreferrer" class="ui-btn-primary">Testar preview</a>
                    <a :href="linkAcesso" x-show="linkAcesso" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary">Abrir link original</a>
                </div>
            </div>
        </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Estado e capa" subtitle="Controle editorial da aula e sua imagem de identificação.">
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_280px]">
            <div class="space-y-4">
                <div>
                    <label class="ui-form-label" for="status">Status</label>
                    <select id="status" name="status" class="ui-form-control" required>
                        @foreach(($statuses ?? []) as $itemStatus)
                            <option value="{{ $itemStatus }}" @selected(old('status', $aula->status ?? 'rascunho') === $itemStatus)>{{ ucfirst($itemStatus) }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="ui-form-label" for="capa">Imagem de capa</label>
                    <input
                        id="capa"
                        name="capa"
                        type="file"
                        accept="image/*"
                        class="ui-form-control"
                        @change="
                            const file = $event.target.files?.[0];
                            if (!file) return;
                            const reader = new FileReader();
                            reader.onload = e => { coverPreview = e.target.result; removeCurrentCover = false; };
                            reader.readAsDataURL(file);
                        "
                    >
                    @error('capa')<p class="ui-form-error">{{ $message }}</p>@enderror

                    @if($isEdit && $coverCurrent)
                        <label class="mt-4 inline-flex items-start gap-3 rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-input-bg)] p-4">
                            <input type="hidden" name="remover_capa" value="0">
                            <input type="checkbox" name="remover_capa" value="1" class="ui-form-check mt-1 rounded" x-model="removeCurrentCover">
                            <span>
                                <span class="block text-sm font-semibold text-[var(--ui-text)]">Remover capa atual</span>
                                <span class="mt-1 block text-xs text-[var(--ui-text-soft)]">A imagem atual será removida no salvamento.</span>
                            </span>
                        </label>
                    @else
                        <input type="hidden" name="remover_capa" value="0">
                    @endif
                </div>
            </div>

            <div class="overflow-hidden rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-subtle)]">
                <div class="aspect-[4/3] bg-[var(--ui-input-bg)]">
                    <template x-if="coverPreview && !removeCurrentCover">
                        <img :src="coverPreview" alt="Prévia da capa da aula" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!coverPreview || removeCurrentCover">
                        <div class="flex h-full w-full items-center justify-center px-6 text-center text-sm text-[var(--ui-text-soft)]">
                            Prévia da capa da aula
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </x-dashboard.section-card>
</div>

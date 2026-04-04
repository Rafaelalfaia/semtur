@php
    $modulo ??= null;
    $isEdit = ($mode ?? 'create') === 'edit';
    $coverCurrent = old('remover_capa') ? null : ($modulo->capa_url ?? null);
@endphp

<div
    x-data="{ coverPreview: @js($coverCurrent), removeCurrentCover: @js((bool) old('remover_capa')) }"
    class="space-y-4"
>
    <x-dashboard.section-card title="Dados principais" subtitle="Estruture o módulo dentro do curso mantendo a mesma hierarquia visual do console.">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="ui-form-label" for="nome">Nome</label>
                <input id="nome" name="nome" type="text" value="{{ old('nome', $modulo->nome ?? '') }}" class="ui-form-control" placeholder="Ex.: Introdução e contexto" required>
                @error('nome')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="ui-form-label" for="slug">Slug</label>
                <input id="slug" name="slug" type="text" value="{{ old('slug', $modulo->slug ?? '') }}" class="ui-form-control" placeholder="deixe vazio para gerar automaticamente">
                @error('slug')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="ui-form-label" for="ordem">Ordem</label>
                <input id="ordem" name="ordem" type="number" min="0" value="{{ old('ordem', $modulo->ordem ?? 0) }}" class="ui-form-control">
                @error('ordem')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="ui-form-label" for="descricao_curta">Descrição curta</label>
                <textarea id="descricao_curta" name="descricao_curta" rows="4" class="ui-form-control" placeholder="Resumo curto do conteúdo deste módulo.">{{ old('descricao_curta', $modulo->descricao_curta ?? '') }}</textarea>
                @error('descricao_curta')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Estado" subtitle="Defina a ordem interna do módulo e seu estado editorial.">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="ui-form-label" for="status">Status</label>
                <select id="status" name="status" class="ui-form-control" required>
                    @foreach(($statuses ?? []) as $itemStatus)
                        <option value="{{ $itemStatus }}" @selected(old('status', $modulo->status ?? 'rascunho') === $itemStatus)>{{ ucfirst($itemStatus) }}</option>
                    @endforeach
                </select>
                @error('status')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>

            @if($isEdit)
                <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-input-bg)] p-4 text-sm text-[var(--ui-text-soft)]">
                    <div class="font-semibold text-[var(--ui-text)]">Aulas vinculadas</div>
                    <div class="mt-1">{{ number_format((int) ($modulo->aulas_count ?? 0)) }} aula(s) cadastradas neste módulo.</div>
                </div>
            @endif
        </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Capa" subtitle="Imagem principal para destacar o módulo na navegação do curso.">
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_280px]">
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

            <div class="overflow-hidden rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-subtle)]">
                <div class="aspect-[4/3] bg-[var(--ui-input-bg)]">
                    <template x-if="coverPreview && !removeCurrentCover">
                        <img :src="coverPreview" alt="Prévia da capa do módulo" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!coverPreview || removeCurrentCover">
                        <div class="flex h-full w-full items-center justify-center px-6 text-center text-sm text-[var(--ui-text-soft)]">
                            Prévia da capa do módulo
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </x-dashboard.section-card>
</div>

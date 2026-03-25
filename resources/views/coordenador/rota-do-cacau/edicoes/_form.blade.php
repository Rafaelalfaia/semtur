@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $coverCurrent = old('remover_capa') ? null : ($edicao->capa_url ?? null);
@endphp

<div
    x-data="{
        coverPreview: @js($coverCurrent),
        removeCover: @js((bool) old('remover_capa')),
        updateCoverPreview(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                this.coverPreview = e.target.result;
                this.removeCover = false;
            };
            reader.readAsDataURL(file);
        }
    }"
    class="space-y-6"
>
    @if($errors->any())
        <div class="ui-alert ui-alert-danger">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <x-dashboard.section-card title="Dados principais" subtitle="Estruture o ano e o conteudo base da edicao">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="ui-form-label">Ano</label>
                        <input type="number" name="ano" min="1900" max="2100" value="{{ old('ano', $edicao->ano ?? now()->year) }}" class="ui-form-control" required>
                    </div>
                    <div>
                        <label class="ui-form-label">Ordem</label>
                        <input type="number" name="ordem" min="0" value="{{ old('ordem', $edicao->ordem ?? 0) }}" class="ui-form-control">
                    </div>
                    <div class="md:col-span-2">
                        <label class="ui-form-label">Titulo</label>
                        <input type="text" name="titulo" value="{{ old('titulo', $edicao->titulo ?? '') }}" class="ui-form-control" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="ui-form-label">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $edicao->slug ?? '') }}" class="ui-form-control" placeholder="deixe vazio para gerar automaticamente">
                        <p class="mt-1 text-xs text-[var(--ui-text-soft)]">Se deixar vazio, o sistema gera automaticamente a partir do titulo.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="ui-form-label">Publicado em</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($edicao->published_at)->format('Y-m-d\\TH:i')) }}" class="ui-form-control">
                    </div>
                    <div class="md:col-span-2">
                        <label class="ui-form-label">Descricao</label>
                        <textarea name="descricao" rows="8" class="ui-form-control" required>{{ old('descricao', $edicao->descricao ?? '') }}</textarea>
                    </div>
                </div>
            </x-dashboard.section-card>
        </div>

        <div class="space-y-6">
            <x-dashboard.section-card title="Publicacao" subtitle="Controle o estado editorial da edicao">
                <div>
                    <label class="ui-form-label">Status</label>
                    <select name="status" class="ui-form-select" required>
                        @foreach($statuses as $itemStatus)
                            <option value="{{ $itemStatus }}" @selected(old('status', $edicao->status ?? 'rascunho') === $itemStatus)>{{ ucfirst($itemStatus) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] px-4 py-4 text-sm text-[var(--ui-text-soft)]">
                    Para publicar, mantenha o texto preenchido e envie uma capa valida para a edicao.
                </div>
            </x-dashboard.section-card>

            <x-dashboard.section-card title="Capa da edicao" subtitle="Imagem usada como destaque da edicao">
                <div>
                    <label class="ui-form-label">Imagem de capa</label>
                    <input type="file" name="capa" accept="image/*" class="ui-form-control" @change="updateCoverPreview($event)">
                    <div class="mt-4 overflow-hidden rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)]">
                        <div class="flex h-56 items-center justify-center">
                            <template x-if="coverPreview && !removeCover">
                                <img :src="coverPreview" alt="Previa da capa" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!coverPreview || removeCover">
                                <div class="text-sm text-[var(--ui-text-soft)]">Previa da capa da edicao</div>
                            </template>
                        </div>
                    </div>

                    @if($isEdit && $coverCurrent)
                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-[var(--ui-text)]">
                            <input type="hidden" name="remover_capa" value="0">
                            <input type="checkbox" name="remover_capa" value="1" x-model="removeCover">
                            Remover capa atual
                        </label>
                    @else
                        <input type="hidden" name="remover_capa" value="0">
                    @endif
                </div>
            </x-dashboard.section-card>

            @if($isEdit)
                <x-dashboard.section-card title="Resumo" subtitle="Contexto rapido desta edicao">
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--ui-text-soft)]">Fotos cadastradas</dt>
                            <dd class="font-medium text-[var(--ui-text-title)]">{{ $edicao->fotos_count ?? 0 }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--ui-text-soft)]">Videos cadastrados</dt>
                            <dd class="font-medium text-[var(--ui-text-title)]">{{ $edicao->videos_count ?? 0 }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--ui-text-soft)]">Patrocinadores</dt>
                            <dd class="font-medium text-[var(--ui-text-title)]">{{ $edicao->patrocinadores_count ?? 0 }}</dd>
                        </div>
                    </dl>
                </x-dashboard.section-card>
            @endif
        </div>
    </section>
</div>

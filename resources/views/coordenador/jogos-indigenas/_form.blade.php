@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $profileCurrent = old('remover_foto_perfil') ? null : ($jogo->foto_perfil_url ?? null);
    $coverCurrent = old('remover_foto_capa') ? null : ($jogo->foto_capa_url ?? null);
@endphp

<div
    x-data="{
        profilePreview: @js($profileCurrent),
        coverPreview: @js($coverCurrent),
        removeProfile: @js((bool) old('remover_foto_perfil')),
        removeCover: @js((bool) old('remover_foto_capa')),
        updatePreview(event, target) {
            const file = event.target.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                this[target] = e.target.result;
                if (target === 'profilePreview') this.removeProfile = false;
                if (target === 'coverPreview') this.removeCover = false;
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
            <x-dashboard.section-card title="Dados principais" subtitle="Estruture a base editorial do jogo principal">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="ui-form-label">Título</label>
                        <input type="text" name="titulo" value="{{ old('titulo', $jogo->titulo ?? '') }}" class="ui-form-control" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="ui-form-label">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $jogo->slug ?? '') }}" class="ui-form-control" placeholder="deixe vazio para gerar automaticamente">
                    </div>
                    <div>
                        <label class="ui-form-label">Ordem</label>
                        <input type="number" name="ordem" min="0" value="{{ old('ordem', $jogo->ordem ?? 0) }}" class="ui-form-control">
                    </div>
                    <div>
                        <label class="ui-form-label">Publicado em</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($jogo->published_at)->format('Y-m-d\\TH:i')) }}" class="ui-form-control">
                    </div>
                    <div class="md:col-span-2">
                        <label class="ui-form-label">Descrição</label>
                        <textarea name="descricao" rows="8" class="ui-form-control" required>{{ old('descricao', $jogo->descricao ?? '') }}</textarea>
                    </div>
                </div>
            </x-dashboard.section-card>

            <x-dashboard.section-card title="Imagens" subtitle="Defina perfil e capa preservando a imagem principal para o site futuro">
                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <label class="ui-form-label">Foto de perfil</label>
                        <input type="file" name="foto_perfil" accept="image/*" class="ui-form-control" @change="updatePreview($event, 'profilePreview')">
                        <div class="mt-4 overflow-hidden rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)]">
                            <div class="flex h-56 items-center justify-center">
                                <template x-if="profilePreview && !removeProfile">
                                    <img :src="profilePreview" alt="Prévia da foto de perfil" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!profilePreview || removeProfile">
                                    <div class="text-sm text-[var(--ui-text-soft)]">Prévia da foto de perfil</div>
                                </template>
                            </div>
                        </div>

                        @if($isEdit && $profileCurrent)
                            <label class="mt-3 inline-flex items-center gap-2 text-sm text-[var(--ui-text)]">
                                <input type="hidden" name="remover_foto_perfil" value="0">
                                <input type="checkbox" name="remover_foto_perfil" value="1" x-model="removeProfile">
                                Remover foto de perfil atual
                            </label>
                        @else
                            <input type="hidden" name="remover_foto_perfil" value="0">
                        @endif
                    </div>

                    <div>
                        <label class="ui-form-label">Foto de capa</label>
                        <input type="file" name="foto_capa" accept="image/*" class="ui-form-control" @change="updatePreview($event, 'coverPreview')">
                        <div class="mt-4 overflow-hidden rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)]">
                            <div class="flex h-56 items-center justify-center">
                                <template x-if="coverPreview && !removeCover">
                                    <img :src="coverPreview" alt="Prévia da foto de capa" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!coverPreview || removeCover">
                                    <div class="text-sm text-[var(--ui-text-soft)]">Prévia da foto de capa</div>
                                </template>
                            </div>
                        </div>

                        @if($isEdit && $coverCurrent)
                            <label class="mt-3 inline-flex items-center gap-2 text-sm text-[var(--ui-text)]">
                                <input type="hidden" name="remover_foto_capa" value="0">
                                <input type="checkbox" name="remover_foto_capa" value="1" x-model="removeCover">
                                Remover foto de capa atual
                            </label>
                        @else
                            <input type="hidden" name="remover_foto_capa" value="0">
                        @endif
                    </div>
                </div>
            </x-dashboard.section-card>
        </div>

        <div class="space-y-6">
            <x-dashboard.section-card title="Publicação" subtitle="Controle o estado editorial do jogo">
                <div>
                    <label class="ui-form-label">Status</label>
                    <select name="status" class="ui-form-select" required>
                        @foreach($statuses as $itemStatus)
                            <option value="{{ $itemStatus }}" @selected(old('status', $jogo->status ?? 'rascunho') === $itemStatus)>{{ ucfirst($itemStatus) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] px-4 py-4 text-sm text-[var(--ui-text-soft)]">
                    Para publicar, mantenha a descrição preenchida e envie foto de perfil e foto de capa.
                </div>
            </x-dashboard.section-card>

            @if($isEdit)
                <x-dashboard.section-card title="Resumo" subtitle="Contexto rápido do módulo">
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--ui-text-soft)]">Slug atual</dt>
                            <dd class="font-medium text-[var(--ui-text-title)]">{{ $jogo->slug }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--ui-text-soft)]">Edições cadastradas</dt>
                            <dd class="font-medium text-[var(--ui-text-title)]">{{ $jogo->edicoes_count ?? 0 }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--ui-text-soft)]">Última publicação</dt>
                            <dd class="font-medium text-[var(--ui-text-title)]">{{ optional($jogo->published_at)->format('d/m/Y H:i') ?: '—' }}</dd>
                        </div>
                    </dl>
                </x-dashboard.section-card>
            @endif
        </div>
    </section>
</div>

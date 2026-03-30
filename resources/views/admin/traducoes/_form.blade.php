@php
    $translation ??= null;
    $translationValues ??= [];
@endphp

<x-dashboard.section-card title="Dados principais" subtitle="Cadastre a chave e o texto base que servirão de origem para as traduções do sistema.">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="ui-form-label" for="key">Chave</label>
            <input id="key" name="key" type="text" value="{{ old('key', $translation->key ?? '') }}" class="ui-form-control" placeholder="home.hero.title" required>
            <p class="mt-1 text-xs text-[var(--ui-text-soft)]">Use um padrão estável por módulo, bloco e campo.</p>
            @error('key')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="ui-form-label" for="group">Grupo</label>
            <input id="group" name="group" type="text" value="{{ old('group', $translation->group ?? '') }}" class="ui-form-control" placeholder="home">
            @error('group')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-4 grid gap-4">
        <div>
            <label class="ui-form-label" for="description">Descrição</label>
            <input id="description" name="description" type="text" value="{{ old('description', $translation->description ?? '') }}" class="ui-form-control" placeholder="Título principal da home">
            @error('description')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="ui-form-label" for="base_text">Texto base</label>
            <textarea id="base_text" name="base_text" rows="4" class="ui-form-control" placeholder="Descubra Altamira" required>{{ old('base_text', $translation->base_text ?? '') }}</textarea>
            @error('base_text')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
    </div>
</x-dashboard.section-card>

<x-dashboard.section-card title="Traduções por idioma" subtitle="Preencha os idiomas ativos sem alterar ainda a leitura pública do frontend.">
    <div class="grid gap-4">
        @forelse($idiomas as $idioma)
            <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-input-bg)] p-4">
                <div class="mb-3 flex items-center gap-3">
                    @if($idioma->bandeira_url)
                        <img src="{{ $idioma->bandeira_url }}" alt="" class="h-8 w-8 rounded-full border border-[var(--ui-border)] object-cover">
                    @else
                        <div class="flex h-8 w-8 items-center justify-center rounded-full border border-[var(--ui-border)] bg-[var(--ui-panel)] text-xs font-semibold text-[var(--ui-text-soft)]">
                            {{ $idioma->sigla }}
                        </div>
                    @endif

                    <div>
                        <div class="text-sm font-semibold text-[var(--ui-text)]">{{ $idioma->nome }}</div>
                        <div class="text-xs text-[var(--ui-text-soft)]">{{ $idioma->codigo }}</div>
                    </div>

                    @if($idioma->is_default)
                        <span class="ui-badge ui-badge-success ml-auto">Base padrão</span>
                    @endif
                </div>

                <textarea
                    name="values[{{ $idioma->id }}]"
                    rows="3"
                    class="ui-form-control"
                    placeholder="Texto traduzido para {{ $idioma->nome }}"
                >{{ old("values.{$idioma->id}", $translationValues[$idioma->id] ?? '') }}</textarea>
                @error("values.{$idioma->id}")<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>
        @empty
            <p class="text-sm text-[var(--ui-text-soft)]">Nenhum idioma ativo disponível. Cadastre ou ative um idioma antes de criar traduções.</p>
        @endforelse
    </div>
</x-dashboard.section-card>

<x-dashboard.section-card title="Estado" subtitle="Controle se a chave permanece disponível para o catálogo futuro de traduções do sistema.">
    <label class="inline-flex items-start gap-3 rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-input-bg)] p-4">
        <input type="checkbox" name="is_active" value="1" class="ui-form-check mt-1 rounded" @checked(old('is_active', $translation->is_active ?? true))>
        <span>
            <span class="block text-sm font-semibold text-[var(--ui-text)]">Chave ativa</span>
            <span class="mt-1 block text-xs text-[var(--ui-text-soft)]">Mantém a chave pronta para futura leitura pelo frontend, sem removê-la do catálogo.</span>
        </span>
    </label>
</x-dashboard.section-card>

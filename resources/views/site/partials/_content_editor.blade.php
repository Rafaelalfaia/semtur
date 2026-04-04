@php
    $user = auth()->user();
    $canEditInline = $user
        && method_exists($user, 'can')
        && $user->can('site.manage')
        && Route::has('coordenador.conteudo-site.update');

    $locale = $editorLocale ?? route_locale();
    $blockStatus = old('status', $editableStatus ?? 'publicado');
    $resolvedTranslation = $editableTranslation ?? null;
    $mediaSlot = old('media_slot', $editorMediaSlot ?? 'hero');
    $mediaFieldName = $editorMediaFieldName ?? 'media';
    $removeMediaFieldName = $editorRemoveMediaFieldName ?? 'remover_media';
    $mediaLabel = $editorMediaLabel ?? 'Imagem';
    $mediaPreviewLabel = $editorMediaPreviewLabel ?? 'imagem atual';
    $resolvedMedia = $editableMedia ?? $editableHeroMedia ?? null;
    $triggerVariant = $editorTriggerVariant ?? 'floating';
    $visibleFields = collect($editorFields ?? ['status', 'eyebrow', 'titulo', 'subtitulo', 'lead', 'conteudo', 'cta_label', 'cta_href', 'media', 'seo_title', 'seo_description']);
    $shellClass = match ($triggerVariant) {
        'inline', 'inline-compact' => 'site-editor-trigger-shell site-editor-trigger-shell--inline',
        default => 'site-editor-trigger-shell site-editor-trigger-shell--floating',
    };
    $buttonClass = match ($triggerVariant) {
        'inline' => 'site-editor-trigger site-editor-trigger--inline',
        'inline-compact' => 'site-editor-trigger site-editor-trigger--inline-compact',
        default => 'site-editor-trigger site-editor-trigger--floating',
    };
    $buttonLabel = $editorTriggerLabel ?? ($triggerVariant === 'inline-compact' ? 'Editar' : 'Editar seção');
@endphp

@if($canEditInline)
    <div x-data="{ open: false }" class="{{ $shellClass }}">
        <button
            type="button"
            class="{{ $buttonClass }}"
            @click="open = true"
        >
            <span class="site-editor-trigger-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/>
                </svg>
            </span>
            {{ $buttonLabel }}
        </button>

        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[90] bg-slate-950/45 backdrop-blur-sm"
            @click="open = false"
        ></div>

        <aside
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="fixed inset-y-0 right-0 z-[95] flex w-full max-w-xl flex-col overflow-y-auto border-l border-white/10 bg-slate-950 text-white shadow-2xl"
        >
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Modo edi&ccedil;&atilde;o</div>
                    <h2 class="mt-1 text-lg font-semibold">{{ $editorTitle ?? 'Conte&uacute;do da p&aacute;gina' }}</h2>
                </div>
                <button type="button" class="rounded-full border border-white/10 px-3 py-2 text-sm text-slate-300 hover:bg-white/5" @click="open = false">
                    Fechar
                </button>
            </div>

            <form
                method="POST"
                action="{{ route('coordenador.conteudo-site.update', ['pagina' => $editorPage, 'chave' => $editorKey]) }}"
                enctype="multipart/form-data"
                class="flex-1 space-y-5 px-5 py-5"
            >
                @csrf
                @method('PUT')

                <input type="hidden" name="locale" value="{{ $locale }}">
                <input type="hidden" name="rotulo" value="{{ $editorLabel ?? 'Hero editorial' }}">
                <input type="hidden" name="tipo" value="hero">
                <input type="hidden" name="regiao" value="topo">
                <input type="hidden" name="media_slot" value="{{ $mediaSlot }}">
                @unless($visibleFields->contains('status'))
                    <input type="hidden" name="status" value="{{ $blockStatus }}">
                @endunless

                <div class="grid gap-4 md:grid-cols-2">
                    @if($visibleFields->contains('status'))
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm text-slate-300">Status</label>
                        <select name="status" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm">
                            @foreach(['rascunho', 'publicado', 'arquivado'] as $statusOption)
                                <option value="{{ $statusOption }}" @selected($blockStatus === $statusOption)>{{ ucfirst($statusOption) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if($visibleFields->contains('eyebrow'))
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm text-slate-300">Chamada superior</label>
                        <input name="eyebrow" value="{{ old('eyebrow', $resolvedTranslation?->eyebrow ?? $editableFallback['eyebrow'] ?? null) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm">
                    </div>
                    @endif

                    @if($visibleFields->contains('titulo'))
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm text-slate-300">T&iacute;tulo</label>
                        <input name="titulo" value="{{ old('titulo', $resolvedTranslation?->titulo ?? $editableFallback['titulo'] ?? null) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm">
                    </div>
                    @endif

                    @if($visibleFields->contains('subtitulo'))
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm text-slate-300">Subt&iacute;tulo</label>
                        <input name="subtitulo" value="{{ old('subtitulo', $resolvedTranslation?->subtitulo ?? $editableFallback['subtitulo'] ?? null) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm">
                    </div>
                    @endif

                    @if($visibleFields->contains('lead'))
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm text-slate-300">Resumo</label>
                        <textarea name="lead" rows="4" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm">{{ old('lead', $resolvedTranslation?->lead ?? $editableFallback['lead'] ?? null) }}</textarea>
                    </div>
                    @endif

                    @if($visibleFields->contains('conteudo'))
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm text-slate-300">Conte&uacute;do adicional</label>
                        <textarea name="conteudo" rows="6" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm">{{ old('conteudo', $resolvedTranslation?->conteudo ?? $editableFallback['conteudo'] ?? null) }}</textarea>
                    </div>
                    @endif

                    @if($visibleFields->contains('cta_label'))
                    <div>
                        <label class="mb-1 block text-sm text-slate-300">Texto do bot&atilde;o</label>
                        <input name="cta_label" value="{{ old('cta_label', $resolvedTranslation?->cta_label ?? $editableFallback['cta_label'] ?? null) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm">
                    </div>
                    @endif

                    @if($visibleFields->contains('cta_href'))
                    <div>
                        <label class="mb-1 block text-sm text-slate-300">Link do bot&atilde;o</label>
                        <input name="cta_href" value="{{ old('cta_href', $resolvedTranslation?->cta_href ?? $editableFallback['cta_href'] ?? null) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm">
                    </div>
                    @endif

                    @if($visibleFields->contains('media'))
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm text-slate-300">{{ $mediaLabel }}</label>
                        <input type="file" name="{{ $mediaFieldName }}" accept="image/*" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[var(--ui-primary,#10b981)] file:px-3 file:py-2 file:text-white">
                        @if($resolvedMedia?->url)
                            <img src="{{ $resolvedMedia->url }}" alt="{{ $resolvedMedia->alt_text ?: $mediaPreviewLabel }}" class="mt-3 h-40 w-full rounded-2xl object-cover">
                            <label class="mt-3 flex items-center gap-2 text-sm text-slate-300">
                                <input type="checkbox" name="{{ $removeMediaFieldName }}" value="1" class="rounded border-white/15 bg-white/5">
                                Remover {{ $mediaPreviewLabel }}
                            </label>
                        @endif
                    </div>
                    @endif

                    @if($visibleFields->contains('seo_title'))
                    <div>
                        <label class="mb-1 block text-sm text-slate-300">T&iacute;tulo SEO</label>
                        <input name="seo_title" value="{{ old('seo_title', $resolvedTranslation?->seo_title ?? $editableFallback['seo_title'] ?? null) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm">
                    </div>
                    @endif

                    @if($visibleFields->contains('seo_description'))
                    <div>
                        <label class="mb-1 block text-sm text-slate-300">Descri&ccedil;&atilde;o SEO</label>
                        <input name="seo_description" value="{{ old('seo_description', $resolvedTranslation?->seo_description ?? $editableFallback['seo_description'] ?? null) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm">
                    </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-amber-400/20 bg-amber-400/10 px-4 py-3 text-sm text-amber-100">
                    Salvando em <strong>{{ strtoupper($locale) }}</strong>. Ao salvar em <strong>PT</strong>, o sistema sincroniza <strong>EN</strong> e <strong>ES</strong> automaticamente.
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-white/10 pt-4">
                    <button type="button" class="rounded-xl border border-white/10 px-4 py-2.5 text-sm text-slate-300 hover:bg-white/5" @click="open = false">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                        Salvar conte&uacute;do
                    </button>
                </div>
            </form>
        </aside>
    </div>
@endif

@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $selectedScopes = old('application_scopes', $theme->application_scopes ?? [\App\Models\Theme::SCOPE_GLOBAL]);
    $previewImageUrl = $theme->preview_image_url ?: theme_asset('hero_image', $theme);
@endphp

<div class="space-y-6">
    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <x-dashboard.section-card title="Informações gerais" subtitle="Nome, identidade base e posicionamento administrativo do tema.">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="ui-form-label" for="name">Nome do tema</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $theme->name) }}" class="ui-form-control" required>
                        @error('name')<p class="ui-form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="ui-form-label" for="slug">Slug</label>
                        <input id="slug" name="slug" type="text" value="{{ old('slug', $theme->slug) }}" class="ui-form-control" placeholder="gerado automaticamente se vazio">
                        <p class="mt-1 text-xs text-[var(--ui-text-soft)]">Referência interna e URL administrativa do tema.</p>
                        @error('slug')<p class="ui-form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="ui-form-label" for="type">Tipo</label>
                        <input id="type" name="type" type="text" value="{{ old('type', $theme->type) }}" class="ui-form-control" placeholder="institucional, sazonal, campanha...">
                        @error('type')<p class="ui-form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="ui-form-label" for="base_theme">Tema base</label>
                        <select id="base_theme" name="base_theme" class="ui-form-select">
                            @foreach($baseThemes as $value => $label)
                                <option value="{{ $value }}" @selected(old('base_theme', $theme->base_theme) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('base_theme')<p class="ui-form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="ui-form-label" for="status">Status</label>
                        <select id="status" name="status" class="ui-form-select">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $theme->normalizedStatus()) === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-[var(--ui-text-soft)]">Rascunho para preparação, disponível para uso institucional e arquivado para retirada do ciclo ativo.</p>
                        @error('status')<p class="ui-form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="ui-form-label" for="description">Descrição interna</label>
                        <textarea id="description" name="description" rows="4" class="ui-form-control" placeholder="Explique quando este tema deve ser usado e o tom visual esperado.">{{ old('description', $theme->description) }}</textarea>
                        @error('description')<p class="ui-form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </x-dashboard.section-card>

            <x-dashboard.section-card title="Escopo e vigência" subtitle="Onde o tema se aplica e em qual janela ele pode ser usado com segurança.">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <span class="ui-form-label">Escopos de aplicação</span>
                        <div class="mt-3 grid gap-3 md:grid-cols-2">
                            @foreach($scopeOptions as $scope => $label)
                                <label class="rounded-[20px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] px-4 py-3 text-sm text-[var(--ui-text)]">
                                    <span class="flex items-start gap-3">
                                        <input
                                            type="checkbox"
                                            name="application_scopes[]"
                                            value="{{ $scope }}"
                                            class="mt-1"
                                            @checked(in_array($scope, (array) $selectedScopes, true))
                                        >
                                        <span>
                                            <span class="block font-medium text-[var(--ui-text-title)]">{{ $label }}</span>
                                            <span class="mt-1 block text-xs text-[var(--ui-text-soft)]">
                                                @switch($scope)
                                                    @case(\App\Models\Theme::SCOPE_GLOBAL)
                                                        Válido para console, auth e site quando não houver especialização.
                                                        @break
                                                    @case(\App\Models\Theme::SCOPE_CONSOLE)
                                                        Shell administrativo usado por Admin, Coordenador e Técnico.
                                                        @break
                                                    @case(\App\Models\Theme::SCOPE_SITE)
                                                        Camada pública e institucional do portal.
                                                        @break
                                                    @default
                                                        Experiência de login e autenticação.
                                                @endswitch
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('application_scopes')<p class="ui-form-error">{{ $message }}</p>@enderror
                        @error('application_scopes.*')<p class="ui-form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="ui-form-label" for="starts_at">Início da vigência</label>
                        <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', optional($theme->starts_at)->format('Y-m-d\\TH:i')) }}" class="ui-form-control">
                        @error('starts_at')<p class="ui-form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="ui-form-label" for="ends_at">Fim da vigência</label>
                        <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', optional($theme->ends_at)->format('Y-m-d\\TH:i')) }}" class="ui-form-control">
                        @error('ends_at')<p class="ui-form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </x-dashboard.section-card>

            @include('admin.temas._tokens', ['theme' => $theme, 'tokenGroups' => $tokenGroups])
            @include('admin.temas._assets', ['theme' => $theme, 'assetGroups' => $assetGroups])
            @include('admin.temas._advanced', ['theme' => $theme])
        </div>

        <div class="space-y-6">
            <x-dashboard.section-card title="Governança" subtitle="Regras institucionais do ciclo de vida do tema.">
                <div class="space-y-4 text-sm">
                    <label class="inline-flex items-center gap-3 text-[var(--ui-text)]">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $theme->is_default))>
                        Marcar como tema default de fallback
                    </label>
                    @error('is_default')<p class="ui-form-error">{{ $message }}</p>@enderror

                    <div class="rounded-[20px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4 text-[var(--ui-text-soft)]">
                        O tema default sustenta a queda segura quando o tema ativo manual estiver fora da vigência, incompleto ou indisponível.
                    </div>

                    <div class="rounded-[20px] border border-dashed border-[var(--ui-border)] bg-[var(--ui-surface)] p-4 text-[var(--ui-text-soft)]">
                        O módulo de temas governa identidade visual, tokens, assets, shell, escopos e fallback. Conteúdo operacional continua fora desta área.
                    </div>
                </div>
            </x-dashboard.section-card>

            <x-dashboard.section-card title="Imagem de preview" subtitle="Miniatura administrativa usada na listagem do módulo.">
                <div class="space-y-4">
                    <div class="overflow-hidden rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)]">
                        <div class="flex h-40 items-center justify-center">
                            <img src="{{ $previewImageUrl }}" alt="Preview do tema" class="h-full w-full object-cover">
                        </div>
                    </div>

                    <div>
                        <label class="ui-form-label" for="preview_image">Upload de preview</label>
                        <input id="preview_image" name="preview_image" type="file" accept="image/*" class="ui-form-control">
                        @error('preview_image')<p class="ui-form-error">{{ $message }}</p>@enderror
                    </div>

                    @if($theme->preview_image_path)
                        <label class="inline-flex items-center gap-2 text-sm text-[var(--ui-text-soft)]">
                            <input type="hidden" name="remove_preview_image" value="0">
                            <input type="checkbox" name="remove_preview_image" value="1">
                            Remover imagem de preview atual
                        </label>
                    @else
                        <input type="hidden" name="remove_preview_image" value="0">
                    @endif
                </div>
            </x-dashboard.section-card>

            <x-dashboard.section-card title="Orientações rápidas" subtitle="Como usar esta tela com segurança.">
                <div class="space-y-3 text-sm text-[var(--ui-text-soft)]">
                    <p>Preencha somente o que quiser sobrescrever. Tokens e assets vazios continuam usando o fallback institucional.</p>
                    <p>O preview vale apenas para a sua sessão de Admin e não altera o tema ativo do sistema.</p>
                    <p>Use a área avançada apenas quando precisar de configuração complementar do contrato técnico do tema.</p>
                    @if($isEdit)
                        <p>Depois de salvar, você pode aplicar preview, ativar globalmente ou arquivar o tema sem sair desta área.</p>
                    @endif
                </div>
            </x-dashboard.section-card>
        </div>
    </section>
</div>

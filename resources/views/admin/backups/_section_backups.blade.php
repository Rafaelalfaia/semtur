<div class="space-y-4">
    <div class="grid gap-4 xl:grid-cols-[360px_minmax(0,1fr)]">
        <div class="space-y-4">
            <x-dashboard.section-card title="Gerar agora" subtitle="Crie pacotes locais ou já sincronizados com o R2.">
                <form method="POST" action="{{ route('admin.backups.generate') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="ui-form-label" for="scope">Escopo</label>
                        <select id="scope" name="scope" class="ui-form-select">
                            <option value="all">Completo</option>
                            <option value="database">Somente banco</option>
                            <option value="storage">Somente arquivos públicos</option>
                        </select>
                    </div>
                    <div>
                        <label class="ui-form-label" for="destination">Destino</label>
                        <select id="destination" name="destination" class="ui-form-select">
                            <option value="local">Gerar só local</option>
                            <option value="remote">Gerar e enviar ao R2</option>
                        </select>
                    </div>
                    <button class="ui-btn-primary">Executar backup</button>
                </form>
            </x-dashboard.section-card>

            <x-dashboard.section-card title="Importar pacote" subtitle="Adicione um .zip à biblioteca local sem restaurar nada no sistema.">
                <form method="POST" action="{{ route('admin.backups.import-package') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="ui-form-label" for="package">Pacote .zip</label>
                        <input id="package" name="package" type="file" accept=".zip,application/zip" class="ui-form-control" required>
                    </div>
                    <button class="ui-btn-secondary">Importar para a biblioteca local</button>
                </form>
            </x-dashboard.section-card>
        </div>

        <div class="space-y-4">
            <x-dashboard.section-card title="Histórico recente" subtitle="Últimos pacotes vistos na biblioteca local e no R2.">
                @if(empty($history))
                    <div class="ui-empty-state">
                        <div class="ui-empty-state-title">Nenhum evento recente</div>
                        <p class="ui-empty-state-copy">Gere ou sincronize um backup para alimentar o histórico do módulo.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($history as $item)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] px-4 py-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="ui-badge {{ $item['origin'] === 'R2' ? 'ui-badge-warning' : 'ui-badge-neutral' }}">{{ $item['origin'] }}</span>
                                        <span class="truncate text-sm font-medium text-[var(--ui-text-title)]">{{ $item['name'] }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $item['size_label'] }} · {{ $item['modified_at'] ?? 'Sem data' }}</div>
                                </div>
                                <a href="{{ route('admin.backups.index', ['section' => $item['origin'] === 'R2' ? 'remote' : 'backups', 'selected_disk' => $item['origin'] === 'R2' ? 'remote' : 'local', 'selected_path' => $item['path']]) }}" class="ui-btn-secondary">Ver detalhes</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-dashboard.section-card>

            @if($selectedBackup && $selectedBackup['scope'] === 'local')
                <x-dashboard.section-card title="Detalhes do pacote" subtitle="Leitura do manifesto e do contexto técnico do backup selecionado.">
                    <div class="grid gap-3 lg:grid-cols-2">
                        <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
                            <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Arquivo</div>
                            <div class="mt-1 text-sm font-semibold text-[var(--ui-text-title)]">{{ $selectedBackup['name'] }}</div>
                            <div class="mt-2 text-xs text-[var(--ui-text-soft)]">{{ $selectedBackup['path'] }}</div>
                            <div class="mt-2 text-xs text-[var(--ui-text-soft)]">{{ $selectedBackup['size_label'] }} · {{ $selectedBackup['modified_at'] ?? 'Sem data' }}</div>
                        </div>
                        <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
                            <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Origem</div>
                            <div class="mt-1 text-sm font-semibold text-[var(--ui-text-title)]">LOCAL</div>
                            @if($selectedBackup['manifest'])
                                <div class="mt-2 text-xs text-[var(--ui-text-soft)]">Criado em {{ $selectedBackup['manifest']['created_at'] ?? 'Sem data' }}</div>
                                <div class="mt-1 text-xs text-[var(--ui-text-soft)]">App {{ $selectedBackup['manifest']['app']['name'] ?? 'Não informado' }} · {{ $selectedBackup['manifest']['app']['env'] ?? 'env' }}</div>
                            @else
                                <div class="mt-2 text-xs text-[var(--ui-text-soft)]">Manifesto não encontrado no pacote.</div>
                            @endif
                        </div>
                    </div>
                </x-dashboard.section-card>
            @endif

            <x-dashboard.section-card id="backups-local" title="Biblioteca local" subtitle="Pacotes disponíveis no servidor local para download, limpeza ou exportação ao R2.">
                @if(empty($localBackups))
                    <div class="ui-empty-state">
                        <div class="ui-empty-state-title">Nenhum backup local</div>
                        <p class="ui-empty-state-copy">Gere um backup agora ou importe um pacote .zip para iniciar a biblioteca local.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($localBackups as $item)
                            <article class="rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-sm font-semibold text-[var(--ui-text-title)]">{{ $item['name'] }}</h3>
                                        <p class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $item['path'] }}</p>
                                        <p class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $item['size_label'] }} · {{ $item['modified_at'] ?? 'Sem data' }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.backups.index', ['section' => 'backups', 'selected_disk' => 'local', 'selected_path' => $item['path']]) }}" class="ui-btn-secondary">Detalhes</a>
                                        <form method="POST" action="{{ route('admin.backups.download') }}">
                                            @csrf
                                            <input type="hidden" name="path" value="{{ $item['path'] }}">
                                            <button class="ui-btn-secondary">Baixar</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.backups.push-remote') }}">
                                            @csrf
                                            <input type="hidden" name="path" value="{{ $item['path'] }}">
                                            <button class="ui-btn-secondary">Exportar ao R2</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.backups.destroy-local') }}" onsubmit="return confirm('Remover este backup local?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="path" value="{{ $item['path'] }}">
                                            <button class="ui-btn-danger">Apagar local</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </x-dashboard.section-card>
        </div>
    </div>
</div>

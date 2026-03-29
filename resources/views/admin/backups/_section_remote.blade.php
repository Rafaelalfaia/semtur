<div class="space-y-4">
    <x-dashboard.section-card title="Status do R2" subtitle="Leitura objetiva da conexão remota e da política atual do módulo.">
        <div class="grid gap-3 md:grid-cols-3">
            <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Conexão</div>
                <div class="mt-1 text-sm font-semibold text-[var(--ui-text-title)]">{{ $remoteStatus['reachable'] ? 'Online' : ($remoteStatus['configured'] ? 'Instável' : 'Pendente') }}</div>
                <p class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $remoteStatus['message'] }}</p>
            </div>
            <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Bucket</div>
                <div class="mt-1 text-sm font-semibold text-[var(--ui-text-title)]">{{ $remoteConfig['bucket'] ?: 'Não definido' }}</div>
                <p class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $remoteStatus['count'] }} item(ns) visíveis</p>
            </div>
            <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Agenda</div>
                <div class="mt-1 text-sm font-semibold text-[var(--ui-text-title)]">{{ $remoteConfig['auto_enabled'] ? 'Automática' : 'Manual' }}</div>
                <p class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $remoteConfig['time'] }} · local {{ $remoteConfig['keep_local_days'] }}d · remoto {{ $remoteConfig['keep_remote_days'] }}d</p>
            </div>
        </div>

        <div class="mt-4">
            <form method="POST" action="{{ route('admin.backups.test-remote') }}">
                @csrf
                <button class="ui-btn-secondary">Testar conexão R2</button>
            </form>
        </div>
    </x-dashboard.section-card>

    @if($selectedBackup && $selectedBackup['scope'] === 'remote')
        <x-dashboard.section-card title="Detalhes do pacote remoto" subtitle="Leitura do manifesto do pacote selecionado no bucket remoto.">
            <div class="grid gap-3 lg:grid-cols-2">
                <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
                    <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Arquivo</div>
                    <div class="mt-1 text-sm font-semibold text-[var(--ui-text-title)]">{{ $selectedBackup['name'] }}</div>
                    <div class="mt-2 text-xs text-[var(--ui-text-soft)]">{{ $selectedBackup['path'] }}</div>
                    <div class="mt-2 text-xs text-[var(--ui-text-soft)]">{{ $selectedBackup['size_label'] }} · {{ $selectedBackup['modified_at'] ?? 'Sem data' }}</div>
                </div>
                <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
                    <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Origem</div>
                    <div class="mt-1 text-sm font-semibold text-[var(--ui-text-title)]">R2</div>
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

    <x-dashboard.section-card id="backups-r2" title="Biblioteca R2" subtitle="Pacotes já enviados ao bucket remoto, com importação segura para a biblioteca local.">
        @if(empty($remoteBackups))
            <div class="ui-empty-state">
                <div class="ui-empty-state-title">Nenhum backup remoto</div>
                <p class="ui-empty-state-copy">Assim que o envio ao R2 estiver configurado, os pacotes aparecerão aqui para importação local e auditoria.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($remoteBackups as $item)
                    <article class="rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-semibold text-[var(--ui-text-title)]">{{ $item['name'] }}</h3>
                                <p class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $item['path'] }}</p>
                                <p class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $item['size_label'] }} · {{ $item['modified_at'] ?? 'Sem data' }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.backups.index', ['section' => 'remote', 'selected_disk' => 'remote', 'selected_path' => $item['path']]) }}" class="ui-btn-secondary">Detalhes</a>
                                <form method="POST" action="{{ route('admin.backups.pull-remote') }}">
                                    @csrf
                                    <input type="hidden" name="path" value="{{ $item['path'] }}">
                                    <button class="ui-btn-secondary">Importar ao local</button>
                                </form>
                                <form method="POST" action="{{ route('admin.backups.destroy-remote') }}" onsubmit="return confirm('Remover este backup remoto do R2?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="path" value="{{ $item['path'] }}">
                                    <button class="ui-btn-danger">Apagar no R2</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </x-dashboard.section-card>
</div>

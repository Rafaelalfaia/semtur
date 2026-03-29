<div class="space-y-4">
    <div class="grid gap-4 xl:grid-cols-[360px_minmax(0,1fr)]">
        <div class="space-y-4">
            <x-dashboard.section-card title="Leitura rápida" subtitle="Estado atual da biblioteca local, remota e da área operacional.">
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[var(--ui-text-soft)]">Backups locais</dt>
                        <dd class="font-medium text-[var(--ui-text-title)]">{{ $summary['local_total'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[var(--ui-text-soft)]">Backups no R2</dt>
                        <dd class="font-medium text-[var(--ui-text-title)]">{{ $summary['remote_total'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[var(--ui-text-soft)]">Conexão remota</dt>
                        <dd class="font-medium text-[var(--ui-text-title)]">{{ $remoteConfigured ? 'Configurada' : 'Pendente' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[var(--ui-text-soft)]">Peso monitorado</dt>
                        <dd class="font-medium text-[var(--ui-text-title)]">{{ $systemHealth['totals']['label'] }}</dd>
                    </div>
                    <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                        <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Último local</div>
                        <div class="mt-1 text-sm font-medium text-[var(--ui-text-title)]">{{ $summary['last_local']['name'] ?? 'Nenhum pacote' }}</div>
                        <div class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $summary['last_local']['modified_at'] ?? 'Sem registro' }}</div>
                    </div>
                    <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                        <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Último R2</div>
                        <div class="mt-1 text-sm font-medium text-[var(--ui-text-title)]">{{ $summary['last_remote']['name'] ?? 'Nenhum pacote' }}</div>
                        <div class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $summary['last_remote']['modified_at'] ?? 'Sem registro' }}</div>
                    </div>
                </dl>
            </x-dashboard.section-card>

            <x-dashboard.section-card title="Manutenção segura" subtitle="Ações leves para leitura, cache e poda de arquivos regeneráveis.">
                <div class="space-y-3">
                    <form method="POST" action="{{ route('console.cache.clear') }}" onsubmit="return confirm('Limpar caches do sistema agora?');">
                        @csrf
                        <button class="ui-btn-secondary w-full justify-center">Limpar cache do sistema</button>
                    </form>
                    <form method="POST" action="{{ route('admin.backups.audit-media') }}">
                        @csrf
                        <button class="ui-btn-secondary w-full justify-center">Auditar mídia</button>
                    </form>
                    <form method="POST" action="{{ route('admin.backups.prune-safe') }}" onsubmit="return confirm('Podar temporários, derivados e relatórios antigos?');">
                        @csrf
                        <button class="ui-btn-secondary w-full justify-center">Podar temporários seguros</button>
                    </form>
                </div>

                @if($systemHealth['media_cleanup'])
                    <div class="mt-4 rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                        <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Última auditoria de mídia</div>
                        <div class="mt-1 text-sm font-medium text-[var(--ui-text-title)]">{{ $systemHealth['media_cleanup']['generated_at'] ?? 'Sem data' }}</div>
                        <div class="mt-2 grid gap-2 text-xs text-[var(--ui-text-soft)] sm:grid-cols-3">
                            <div>{{ $systemHealth['media_cleanup']['summary']['disk_files'] }} arquivos no disco</div>
                            <div>{{ $systemHealth['media_cleanup']['summary']['referenced_files'] }} referenciados</div>
                            <div>{{ $systemHealth['media_cleanup']['summary']['orphan_files'] }} candidatos a órfão</div>
                        </div>
                    </div>
                @endif
            </x-dashboard.section-card>
        </div>

        <div class="space-y-4">
            <x-dashboard.section-card title="Uso do sistema" subtitle="Leitura de peso, áreas monitoradas e maiores arquivos para ajudar na operação da VPS.">
                <div class="grid gap-3 md:grid-cols-3">
                    <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                        <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Peso monitorado</div>
                        <div class="mt-1 text-sm font-semibold text-[var(--ui-text-title)]">{{ $systemHealth['totals']['label'] }}</div>
                        <p class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ number_format($systemHealth['totals']['files'], 0, ',', '.') }} arquivo(s)</p>
                    </div>
                    <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                        <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Maior área</div>
                        <div class="mt-1 text-sm font-semibold text-[var(--ui-text-title)]">{{ $systemHealth['largest_areas'][0]['label'] ?? 'Sem dados' }}</div>
                        <p class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $systemHealth['largest_areas'][0]['size_label'] ?? '0 B' }}</p>
                    </div>
                    <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                        <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Última leitura</div>
                        <div class="mt-1 text-sm font-semibold text-[var(--ui-text-title)]">{{ $systemHealth['generated_at'] }}</div>
                        <p class="mt-1 text-xs text-[var(--ui-text-soft)]">Atualize a página para renovar a leitura.</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                    <div class="space-y-3">
                        @foreach($systemHealth['areas'] as $area)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] px-4 py-3">
                                <div>
                                    <div class="text-sm font-medium text-[var(--ui-text-title)]">{{ $area['label'] }}</div>
                                    <div class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $area['files_count'] }} arquivo(s) • {{ $area['path'] }}</div>
                                </div>
                                <div class="text-sm font-semibold text-[var(--ui-text-title)]">{{ $area['size_label'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
                        <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Maiores arquivos monitorados</div>
                        @if(empty($systemHealth['largest_files']))
                            <div class="mt-3 text-sm text-[var(--ui-text-soft)]">Nenhum arquivo grande identificado nesta leitura.</div>
                        @else
                            <div class="mt-3 space-y-3">
                                @foreach($systemHealth['largest_files'] as $file)
                                    <div>
                                        <div class="text-sm font-medium text-[var(--ui-text-title)]">{{ $file['size_label'] }}</div>
                                        <div class="mt-1 break-all text-xs text-[var(--ui-text-soft)]">{{ $file['path'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </x-dashboard.section-card>
        </div>
    </div>
</div>

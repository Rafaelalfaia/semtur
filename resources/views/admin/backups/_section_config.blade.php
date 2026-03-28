<div class="space-y-4">
    <x-dashboard.section-card id="backups-config" title="Configuração R2" subtitle="Atualize bucket, endpoint, retenção e agenda automática sem tocar no storage principal do site.">
        <div class="space-y-4">
            <form method="POST" action="{{ route('admin.backups.remote-config.update') }}" class="grid gap-4 lg:grid-cols-2">
                @csrf
                @method('PUT')

                <div class="lg:col-span-2 flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-[var(--ui-text-soft)]">
                        <input type="checkbox" name="remote_enabled" value="1" @checked($remoteConfig['remote_enabled'])>
                        Habilitar envio remoto
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-[var(--ui-text-soft)]">
                        <input type="checkbox" name="auto_enabled" value="1" @checked($remoteConfig['auto_enabled'])>
                        Agendamento automatico
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-[var(--ui-text-soft)]">
                        <input type="checkbox" name="use_path_style" value="1" @checked($remoteConfig['use_path_style'])>
                        Path style endpoint
                    </label>
                </div>

                <div>
                    <label class="ui-form-label" for="remote_disk">Disco remoto</label>
                    <input id="remote_disk" name="remote_disk" type="text" value="{{ old('remote_disk', $remoteConfig['disk']) }}" class="ui-form-control" required>
                </div>
                <div>
                    <label class="ui-form-label" for="time">Horário automático</label>
                    <input id="time" name="time" type="time" value="{{ old('time', $remoteConfig['time']) }}" class="ui-form-control" required>
                </div>
                <div>
                    <label class="ui-form-label" for="bucket">Bucket</label>
                    <input id="bucket" name="bucket" type="text" value="{{ old('bucket', $remoteConfig['bucket']) }}" class="ui-form-control">
                </div>
                <div>
                    <label class="ui-form-label" for="region">Regiao</label>
                    <input id="region" name="region" type="text" value="{{ old('region', $remoteConfig['region']) }}" class="ui-form-control" required>
                </div>
                <div class="lg:col-span-2">
                    <label class="ui-form-label" for="endpoint">Endpoint</label>
                    <input id="endpoint" name="endpoint" type="url" value="{{ old('endpoint', $remoteConfig['endpoint']) }}" class="ui-form-control">
                </div>
                <div class="lg:col-span-2">
                    <label class="ui-form-label" for="url">URL publica opcional</label>
                    <input id="url" name="url" type="url" value="{{ old('url', $remoteConfig['url']) }}" class="ui-form-control">
                </div>
                <div>
                    <label class="ui-form-label" for="access_key_id">Access Key ID</label>
                    <input id="access_key_id" name="access_key_id" type="text" value="{{ old('access_key_id', $remoteConfig['access_key_id']) }}" class="ui-form-control">
                </div>
                <div>
                    <label class="ui-form-label" for="secret_access_key">Secret Access Key</label>
                    <input id="secret_access_key" name="secret_access_key" type="password" value="{{ old('secret_access_key', $remoteConfig['secret_access_key']) }}" class="ui-form-control">
                </div>
                <div>
                    <label class="ui-form-label" for="keep_local_days">Retencao local (dias)</label>
                    <input id="keep_local_days" name="keep_local_days" type="number" min="1" max="365" value="{{ old('keep_local_days', $remoteConfig['keep_local_days']) }}" class="ui-form-control" required>
                </div>
                <div>
                    <label class="ui-form-label" for="keep_remote_days">Retencao remota (dias)</label>
                    <input id="keep_remote_days" name="keep_remote_days" type="number" min="1" max="365" value="{{ old('keep_remote_days', $remoteConfig['keep_remote_days']) }}" class="ui-form-control" required>
                </div>

                <div class="lg:col-span-2">
                    <button class="ui-btn-primary">Salvar configuração</button>
                </div>
            </form>
        </div>
    </x-dashboard.section-card>
</div>

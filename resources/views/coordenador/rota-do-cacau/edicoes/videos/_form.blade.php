<div class="space-y-6">
  @if($errors->any())
    <div class="ui-alert ui-alert-danger">
      <ul class="list-disc space-y-1 pl-5">
        @foreach($errors->all() as $erro)
          <li>{{ $erro }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <x-dashboard.section-card title="Dados do vídeo" subtitle="Use o link original do Google Drive como base do preview.">
      <div class="grid gap-4">
        <div>
          <label class="ui-form-label">Título</label>
          <input type="text" name="titulo" value="{{ old('titulo', $video->titulo ?? '') }}" class="ui-form-control" required>
        </div>
        <div>
          <label class="ui-form-label">Descrição</label>
          <textarea name="descricao" rows="6" class="ui-form-control">{{ old('descricao', $video->descricao ?? '') }}</textarea>
        </div>
        <div>
          <label class="ui-form-label">Drive URL</label>
          <input type="url" name="drive_url" value="{{ old('drive_url', $video->drive_url ?? '') }}" class="ui-form-control" required>
        </div>
        <div>
          <label class="ui-form-label">Embed URL</label>
          <input type="url" name="embed_url" value="{{ old('embed_url', $video->embed_url ?? '') }}" class="ui-form-control" placeholder="opcional">
        </div>
        <div>
          <label class="ui-form-label">Ordem</label>
          <input type="number" name="ordem" min="0" value="{{ old('ordem', $video->ordem ?? 0) }}" class="ui-form-control">
        </div>
      </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Preview" subtitle="O sistema usa o helper do model para resolver o link de preview quando possível.">
      <div class="space-y-3 text-sm text-[var(--ui-text-soft)]">
        <p>Se a URL de embed não for informada, a aplicação tenta gerar automaticamente o preview a partir do link do Google Drive.</p>
        @if(($video->embed_url_resolvida ?? null) || old('embed_url') || old('drive_url'))
          <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] px-4 py-4">
            <div class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--ui-text-soft)]">Preview resolvida</div>
            <div class="mt-2 break-all text-[var(--ui-text)]">
              {{ old('embed_url', $video->embed_url_resolvida ?? ($video->embed_url ?? 'Será resolvida ao salvar.')) }}
            </div>
          </div>
        @endif
      </div>
    </x-dashboard.section-card>
  </section>
</div>

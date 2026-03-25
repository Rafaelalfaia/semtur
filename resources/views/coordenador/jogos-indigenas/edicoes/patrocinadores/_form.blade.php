@php
  $isEdit = ($mode ?? 'create') === 'edit';
  $currentLogo = old('remover_logo') ? null : ($patrocinador->logo_url ?? null);
@endphp

<div
  x-data="{
    preview: @js($currentLogo),
    removeLogo: @js((bool) old('remover_logo')),
    updatePreview(event) {
      const file = event.target.files?.[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => {
        this.preview = e.target.result;
        this.removeLogo = false;
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

  <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <x-dashboard.section-card title="Dados do patrocinador" subtitle="Nome, link e ordem de exibicao.">
      <div class="grid gap-4">
        <div>
          <label class="ui-form-label">Nome</label>
          <input type="text" name="nome" value="{{ old('nome', $patrocinador->nome ?? '') }}" class="ui-form-control" required>
        </div>
        <div>
          <label class="ui-form-label">URL</label>
          <input type="url" name="url" value="{{ old('url', $patrocinador->url ?? '') }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Ordem</label>
          <input type="number" name="ordem" min="0" value="{{ old('ordem', $patrocinador->ordem ?? 0) }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Logo</label>
          <input type="file" name="logo" accept="image/*" class="ui-form-control" @change="updatePreview($event)">
          @if($isEdit)
            <label class="mt-3 inline-flex items-center gap-2 text-sm text-[var(--ui-text)]">
              <input type="hidden" name="remover_logo" value="0">
              <input type="checkbox" name="remover_logo" value="1" x-model="removeLogo">
              Remover logo atual
            </label>
          @else
            <input type="hidden" name="remover_logo" value="0">
          @endif
        </div>
      </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Preview" subtitle="Visualizacao da logo do patrocinador nesta edicao.">
      <div class="overflow-hidden rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)]">
        <div class="flex h-80 items-center justify-center">
          <template x-if="preview && !removeLogo">
            <img :src="preview" alt="Preview da logo" class="h-full w-full object-contain p-6">
          </template>
          <template x-if="!preview || removeLogo">
            <div class="px-6 text-center text-sm text-[var(--ui-text-soft)]">Nenhuma logo selecionada no momento.</div>
          </template>
        </div>
      </div>
    </x-dashboard.section-card>
  </section>
</div>

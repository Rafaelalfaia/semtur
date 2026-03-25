@php
  $isEdit = ($mode ?? 'create') === 'edit';
  $currentImage = $foto->imagem_url ?? null;
@endphp

<div
  x-data="{
    preview: @js($currentImage),
    updatePreview(event) {
      const file = event.target.files?.[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => this.preview = e.target.result;
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
    <x-dashboard.section-card title="Dados da foto" subtitle="Imagem, legenda opcional e ordem da galeria.">
      <div class="grid gap-4">
        <div>
          <label class="ui-form-label">Legenda</label>
          <input type="text" name="legenda" value="{{ old('legenda', $foto->legenda ?? '') }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Ordem</label>
          <input type="number" name="ordem" min="0" value="{{ old('ordem', $foto->ordem ?? 0) }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Imagem</label>
          <input type="file" name="imagem" accept="image/*" class="ui-form-control" @change="updatePreview($event)" {{ $isEdit ? '' : 'required' }}>
        </div>
      </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Preview" subtitle="Visualizacao da foto que sera exibida na galeria da edicao.">
      <div class="overflow-hidden rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)]">
        <div class="flex h-80 items-center justify-center">
          <template x-if="preview">
            <img :src="preview" alt="Preview da foto" class="h-full w-full object-cover">
          </template>
          <template x-if="!preview">
            <div class="px-6 text-center text-sm text-[var(--ui-text-soft)]">Selecione uma imagem para visualizar a foto da galeria.</div>
          </template>
        </div>
      </div>
    </x-dashboard.section-card>
  </section>
</div>

@php
  $aviso = $aviso ?? new \App\Models\Conteudo\Aviso();
  $statuses = ['publicado'=>'Publicado','rascunho'=>'Rascunho','arquivado'=>'Arquivado'];
@endphp

<div class="ui-aviso-form-grid">
  <div class="space-y-5">
    <x-dashboard.section-card title="Conteudo do aviso" subtitle="Titulo, descricao e contato rapido" class="ui-coord-dashboard-panel">
      <div class="space-y-4">
        <div>
          <label class="ui-form-label">Titulo *</label>
          <input type="text" name="titulo" value="{{ old('titulo', data_get($aviso,'titulo','')) }}" class="ui-form-control" required>
          @error('titulo')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="ui-form-label">Descricao *</label>
          <textarea name="descricao" rows="6" class="ui-form-control ui-aviso-textarea" required>{{ old('descricao', data_get($aviso,'descricao','')) }}</textarea>
          @error('descricao')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div class="md:col-span-2">
            <label class="ui-form-label">WhatsApp (apenas numeros, com DDI/DDD)</label>
            <input type="text" name="whatsapp" placeholder="5593999998888" value="{{ old('whatsapp', data_get($aviso,'whatsapp','')) }}" class="ui-form-control">
            @error('whatsapp')<p class="ui-form-error">{{ $message }}</p>@enderror
            <p class="ui-profile-help mt-2">Sera usado no botao “Falar no WhatsApp”.</p>
          </div>

          <div>
            <label class="ui-form-label">Status *</label>
            <select name="status" class="ui-form-select" required>
              @foreach($statuses as $k=>$v)
                <option value="{{ $k }}" @selected(old('status', data_get($aviso,'status','publicado')) === $k)>{{ $v }}</option>
              @endforeach
            </select>
            @error('status')<p class="ui-form-error">{{ $message }}</p>@enderror
          </div>
        </div>
      </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Janela de exibicao" subtitle="Defina quando o aviso deve ficar visivel" class="ui-coord-dashboard-panel">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
          <label class="ui-form-label">Inicio da exibicao</label>
          <input type="datetime-local" name="inicio_em" value="{{ old('inicio_em', optional(data_get($aviso,'inicio_em'))->format('Y-m-d\TH:i')) }}" class="ui-form-control">
          @error('inicio_em')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="ui-form-label">Fim da exibicao</label>
          <input type="datetime-local" name="fim_em" value="{{ old('fim_em', optional(data_get($aviso,'fim_em'))->format('Y-m-d\TH:i')) }}" class="ui-form-control">
          @error('fim_em')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
      </div>
    </x-dashboard.section-card>
  </div>

  <div class="space-y-5">
    <x-dashboard.section-card title="Imagem do aviso" subtitle="Upload opcional com preview e remocao" class="ui-coord-dashboard-panel">
      <input type="file" name="imagem" accept="image/*" class="ui-banner-highlight-file">
      @error('imagem')<p class="ui-form-error mt-2">{{ $message }}</p>@enderror
      <p class="ui-profile-help mt-2">Envie uma imagem para enriquecer a leitura do aviso.</p>

      @if(($aviso->exists ?? false) && !empty($aviso->imagem_path))
        <div class="ui-aviso-image-card mt-4">
          <img src="{{ Storage::url($aviso->imagem_path) }}" alt="" class="ui-aviso-image-preview">
          <button
            type="button"
            onclick="if(confirm('Remover imagem atual?')) document.getElementById('aviso-remove-imagem-form').submit();"
            class="ui-btn-secondary"
          >
            Remover imagem
          </button>
        </div>
      @endif
    </x-dashboard.section-card>
  </div>
</div>

<div class="pt-2 flex items-center justify-end gap-3">
  <a href="{{ route('coordenador.avisos.index') }}" class="ui-btn-secondary">Cancelar</a>
  <button type="submit" class="ui-btn-primary">
    {{ ($mode ?? 'create') === 'edit' ? 'Salvar alteracoes' : 'Criar aviso' }}
  </button>
</div>

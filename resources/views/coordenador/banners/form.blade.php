@extends('console.layout')

@section('title', ($banner->exists ? 'Editar' : 'Novo').' banner - Console')
@section('page.title', $banner->exists ? 'Editar banner' : 'Novo banner')
@section('topbar.description', 'Cadastre ou atualize banners secundarios mantendo consistencia com o shell, o modo global e a futura camada de temas.')

@section('topbar.nav')
  <a href="{{ route('coordenador.banners.index') }}" class="ui-console-topbar-tab">Banners</a>
  <span class="ui-console-topbar-tab is-active">{{ $banner->exists ? 'Editar banner' : 'Novo banner' }}</span>
@endsection

@section('content')
@php
  $bx = max(0, min(100, (float) old('pos_banner_x', data_get($banner, 'pos_banner_x', 50))));
  $by = max(0, min(100, (float) old('pos_banner_y', data_get($banner, 'pos_banner_y', 50))));
@endphp

<div class="ui-console-page">
  @if($errors->any())
    <div class="ui-alert ui-alert-danger mb-4">
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <x-dashboard.page-header
    title="{{ $banner->exists ? 'Editar banner' : 'Novo banner' }}"
    subtitle="Configure conteudo, status e imagem do banner em um formulario alinhado ao novo padrao visual do console."
  />

  <form method="post" enctype="multipart/form-data" action="{{ $banner->exists ? route('coordenador.banners.update',$banner) : route('coordenador.banners.store') }}" class="mt-5 space-y-5">
    @csrf
    @if($banner->exists)
      @method('PUT')
    @endif

    <div class="ui-banner-module-form-grid">
      <div class="space-y-5">
        <x-dashboard.section-card title="Conteudo do banner" subtitle="Texto principal, CTA e ordem de exibicao" class="ui-coord-dashboard-panel">
          <div class="grid gap-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
              <div class="md:col-span-2">
                <label class="ui-form-label">Titulo</label>
                <input name="titulo" value="{{ old('titulo',$banner->titulo) }}" class="ui-form-control" placeholder="Jogos Indigenas">
              </div>
              <div>
                <label class="ui-form-label">Ordem</label>
                <input type="number" name="ordem" min="0" value="{{ old('ordem',$banner->ordem ?? 0) }}" class="ui-form-control">
              </div>
            </div>

            <div>
              <label class="ui-form-label">Subtitulo (opcional)</label>
              <input name="subtitulo" value="{{ old('subtitulo',$banner->subtitulo) }}" class="ui-form-control" placeholder="Festival anual...">
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="ui-form-label">Texto do botao (CTA)</label>
                <input name="cta_label" value="{{ old('cta_label',$banner->cta_label) }}" class="ui-form-control" placeholder="Saiba mais">
              </div>
              <div>
                <label class="ui-form-label">URL do botao</label>
                <input name="cta_url" value="{{ old('cta_url',$banner->cta_url) }}" class="ui-form-control" placeholder="https://...">
              </div>
            </div>
          </div>
        </x-dashboard.section-card>

        <x-dashboard.section-card title="Publicacao" subtitle="Status atual e data de publicacao" class="ui-coord-dashboard-panel">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="ui-form-label">Status</label>
              <select name="status" class="ui-form-select">
                @foreach(['rascunho'=>'Rascunho','publicado'=>'Publicado','arquivado'=>'Arquivado'] as $k=>$v)
                  <option value="{{ $k }}" @selected(old('status',$banner->status)===$k)>{{ $v }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="ui-form-label">Publicado em</label>
              <input value="{{ optional($banner->published_at)->format('d/m/Y H:i') }}" disabled class="ui-form-control">
            </div>
          </div>
        </x-dashboard.section-card>
      </div>

      <div class="space-y-5">
        <x-dashboard.section-card title="Imagem do banner" subtitle="Formato recomendado 3:1, como 1800x600" class="ui-coord-dashboard-panel">
          <input type="hidden" name="pos_banner_x" id="pos_banner_x" value="{{ $bx }}">
          <input type="hidden" name="pos_banner_y" id="pos_banner_y" value="{{ $by }}">
          <input type="hidden" name="pos_banner" id="pos_banner" value="{{ $bx }},{{ $by }}">

          <input id="imagem" type="file" name="imagem" accept=".jpg,.jpeg,.png,.webp" class="ui-banner-highlight-file">
          <p class="ui-profile-help mt-2">Arquivos de ate 4MB. Arraste para ajustar o foco e use duplo clique para centralizar.</p>

          <div id="preview-banner" class="ui-banner-highlight-preview mt-4" style="aspect-ratio: 3 / 1;">
            <img
              id="preview-banner-img"
              src="{{ ($banner->exists && $banner->imagem_url && !old('imagem')) ? $banner->imagem_url : '' }}"
              data-src-processed="{{ $banner->imagem_url ?? '' }}"
              data-src-original="{{ $banner->imagem_original_url ?? ($banner->imagem_url ?? '') }}"
              alt="Previa do banner"
              style="object-position: {{ $bx }}% {{ $by }}%;"
              class="w-full h-full object-cover {{ ($banner->exists && $banner->imagem_url && !old('imagem')) ? '' : 'hidden' }}"
            >
          </div>
        </x-dashboard.section-card>
      </div>
    </div>

    <div class="flex items-center justify-between gap-3">
      <a href="{{ route('coordenador.banners.index') }}" class="ui-btn-secondary">Voltar</a>
      <button type="submit" class="ui-btn-primary">
        {{ $banner->exists ? 'Salvar alteracoes' : 'Criar banner' }}
      </button>
    </div>
  </form>

  @if($banner->exists)
    <form action="{{ route('coordenador.banners.destroy',$banner) }}" method="post" onsubmit="return confirm('Remover este banner?')" class="mt-4 text-right">
      @csrf
      @method('DELETE')
      <button class="ui-btn-danger">Excluir</button>
    </form>
  @endif
</div>
@endsection

@push('scripts')
  @vite('resources/js/simple-previews.js')
@endpush

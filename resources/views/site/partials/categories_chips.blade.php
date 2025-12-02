@php
  /** @var \Illuminate\Support\Collection|\App\Models\Catalogo\Categoria[] $categorias */
  $categorias = ($categorias ?? collect()) instanceof \Illuminate\Support\Collection ? $categorias : collect($categorias);
@endphp

@if($categorias->isNotEmpty())
  <div class="h-scroll flex gap-2 overflow-x-auto py-2 px-1">
    <button type="button" class="card-sq" title="Todos"
      onclick="window.dispatchEvent(new CustomEvent('mapa:set-categoria',{ detail: null }))">
      <img src="{{ asset('images/placeholder-categoria.png') }}" alt="Todos" loading="lazy">
      <div class="grad"></div><div class="label">Todos</div>
    </button>

    @foreach($categorias as $cat)
      <button type="button" class="card-sq" title="{{ $cat->nome }}"
        onclick="window.dispatchEvent(new CustomEvent('mapa:set-categoria',{ detail: @json($cat->slug) }))">
        <img src="{{ $cat->icone_url }}" alt="{{ $cat->nome }}" loading="lazy">
        <div class="grad"></div><div class="label">{{ $cat->nome }}</div>
      </button>
    @endforeach
  </div>
@endif

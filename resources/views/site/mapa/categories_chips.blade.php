@php
  /** @var \Illuminate\Support\Collection|\App\Models\Catalogo\Categoria[]|array|null $categorias */
  // corrige o typo e garante coleção
  $categorias = $categorias ?? collect();
  if (! $categorias instanceof \Illuminate\Support\Collection) {
      $categorias = collect($categorias);
  }
@endphp

@if($categorias->isNotEmpty())
  <div class="h-scroll">
    {{-- Chip "Todos" (limpa filtro) --}}
    <button type="button"
            class="card-sq"
            title="Todos"
            onclick="window.dispatchEvent(new CustomEvent('mapa:set-categoria',{ detail: null }))">
      <img src="{{ asset('images/placeholder-card.jpg') }}" alt="Todos" loading="lazy">
      <div class="grad"></div>
      <div class="label">Todos</div>
    </button>

    @foreach($categorias as $cat)
      @continue(empty($cat->slug) || empty($cat->nome))

      @php
        $src = $cat->icone_url
            ?? ($cat->icone_path ? \Illuminate\Support\Facades\Storage::url($cat->icone_path) : asset('images/placeholder-card.jpg'));
      @endphp

      <button type="button"
              class="card-sq"
              title="{{ $cat->nome }}"
              onclick="window.dispatchEvent(new CustomEvent('mapa:set-categoria',{ detail: @json($cat->slug) }))">
        <img src="{{ $src }}" alt="{{ $cat->nome }}" loading="lazy">
        <div class="grad"></div>
        <div class="label">{{ $cat->nome }}</div>
      </button>
    @endforeach
  </div>
@endif

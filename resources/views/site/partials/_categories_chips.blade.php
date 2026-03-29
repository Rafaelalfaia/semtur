@php
    $activeSlug = $activeSlug ?? request('categoria') ?? request('cat') ?? ($currentCat->slug ?? null);
    $linkFor = $href ?? fn($cat) => localized_route('site.explorar', ['categoria' => $cat->slug]);
@endphp

<div class="site-chips-shell">
    <div class="site-chips-scroll" role="list" aria-label="Categorias" @if(!empty($scrollId)) id="{{ $scrollId }}" @endif>
        @forelse($categorias as $categoria)
            @php
                $isActive = $activeSlug === ($categoria->slug ?? null);
            @endphp

            <a href="{{ $linkFor($categoria) }}"
               class="{{ $isActive ? 'site-chip site-chip-active' : 'site-chip' }}"
               aria-label="Categoria {{ $categoria->nome }}"
               @if($isActive) aria-current="page" @endif>
                @if(! empty($categoria->icone_path))
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($categoria->icone_path) }}"
                         alt="{{ $categoria->nome }}"
                         loading="lazy"
                         decoding="async"
                         class="site-chip-icon">
                @endif
                <span>{{ $categoria->nome }}</span>
            </a>
        @empty
            <div class="site-empty-state">
                <div class="site-empty-state-copy">Sem categorias publicadas.</div>
            </div>
        @endforelse
    </div>
</div>

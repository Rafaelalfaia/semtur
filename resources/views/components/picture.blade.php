@props([
  'jpg',              // obrigatório: URL JPG/JPEG (ex.: Storage::url(...))
  'webp' => null,     // opcional: URL WebP
  'avif' => null,     // opcional: URL AVIF
  'alt'  => '',
  'class'=> '',       // classes aplicadas no <img>
  'priority' => false,// true = eager + fetchpriority=high (acima da dobra)
  'sizes' => '(max-width: 640px) 100vw, 33vw', // ajuste conforme seu grid
])

<picture>
  @if($avif)
    <source type="image/avif" srcset="{{ $avif }}" sizes="{{ $sizes }}">
  @endif
  @if($webp)
    <source type="image/webp" srcset="{{ $webp }}" sizes="{{ $sizes }}">
  @endif

  <img
    src="{{ $jpg }}"
    alt="{{ $alt }}"
    @class([$class])
    loading="{{ $priority ? 'eager' : 'lazy' }}"
    decoding="async"
    @if($priority) fetchpriority="high" @endif
    sizes="{{ $sizes }}"
  >
</picture>

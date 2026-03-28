@props([
  'jpg',
  'webp' => null,
  'avif' => null,
  'alt' => '',
  'class' => '',
  'priority' => false,
  'sizes' => '(max-width: 640px) 100vw, 33vw',
  'width' => null,
  'height' => null,
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
    @if($width) width="{{ $width }}" @endif
    @if($height) height="{{ $height }}" @endif
  >
</picture>

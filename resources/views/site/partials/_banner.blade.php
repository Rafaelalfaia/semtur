@if($banner)
  @php
    $url   = $banner->imagem_url;
    $href  = $banner->cta_url;
    $title = $banner->titulo ?? 'Banner';
  @endphp

  @if($url)
    @if($href)
      <a href="{{ $href }}" aria-label="{{ $title }}" class="block w-full h-full">
        <img src="{{ $url }}" alt="{{ $title }}" loading="lazy" decoding="async">
      </a>
    @else
      <img src="{{ $url }}" alt="{{ $title }}" loading="lazy" decoding="async">
    @endif
  @else
    {{-- placeholder quando não há imagem --}}
    <div class="w-full h-full bg-slate-200"></div>
  @endif
@endif

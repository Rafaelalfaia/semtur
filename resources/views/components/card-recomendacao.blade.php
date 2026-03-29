@props([
  'title',
  'subtitle' => null,
  'image'    => null,
  'href'     => '#',
  'class'    => '',
])

@php
  use Illuminate\Support\Str;

  $computedHref = is_string($href) ? $href : '#';

  // Corrigir links internos que apontam para /explorar?ponto=... ou ?empresa=...
  try {
      if (is_string($computedHref) && Str::startsWith($computedHref, ['/explorar','explorar'])) {
          $parts = parse_url($computedHref);
          $q = [];
          if (!empty($parts['query'])) parse_str($parts['query'], $q);

          if (!empty($q['ponto'])) {
              $computedHref = localized_route('site.ponto', ['ponto' => $q['ponto']]);
          } elseif (!empty($q['empresa'])) {
              $computedHref = localized_route('site.empresa', ['empresa' => $q['empresa']]);
          }
      }
  } catch (\Throwable $e) {
      // se não houver rota/param, mantém o href recebido
  }
@endphp

<a href="{{ $computedHref }}"
   {{ $attributes->merge([
      'class' => "block relative rounded-[10px] overflow-hidden shadow-[0_10px_24px_rgba(0,0,0,0.20)] w-full h-[192px] $class"
   ]) }}>
  {{-- imagem --}}
  @if($image)
    <img src="{{ $image }}" alt="{{ $title }}"
         class="absolute inset-0 w-full h-full object-cover" loading="lazy" decoding="async">
  @else
    <div class="absolute inset-0 bg-slate-200"></div>
  @endif

  {{-- overlay (metade inferior) --}}
  <div class="absolute left-0 right-0 bottom-0 h-[83px]
              bg-gradient-to-b from-transparent to-black/100"></div>

  {{-- textos --}}
  <div class="absolute left-2.5 right-2.5 bottom-2.5 flex flex-col gap-2">
    <div class="text-white font-semibold leading-5 text-[16px] line-clamp-1">{{ $title }}</div>
    @if($subtitle)
      <div class="flex items-center gap-1.5 text-white text-[12px] leading-[18px]">
        <svg class="w-[14px] h-[14px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5Z"/>
        </svg>
        <span class="drop-shadow-[0_1px_2px_rgba(0,0,0,0.45)]">{{ $subtitle }}</span>
      </div>
    @endif
  </div>
</a>

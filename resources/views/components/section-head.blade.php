@props(['title','href'=>null,'label'=>'Ver todos'])
<div class="flex items-center justify-between mb-3 px-4">
  <h2 class="text-base font-semibold">{{ $title }}</h2>
  @if($href)
    <a href="{{ $href }}" class="text-emerald-700 text-sm hover:underline">{{ $label }}</a>
  @endif
</div>

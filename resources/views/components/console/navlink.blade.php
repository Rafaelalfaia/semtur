@props(['active' => false, 'href' => '#'])
@php
$cls = $active
  ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/20'
  : 'text-slate-300 hover:text-white hover:bg-white/5 border border-transparent';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class'=>"block rounded-lg px-3 py-2 text-sm {$cls}"]) }}>
  {{ $slot }}
</a>

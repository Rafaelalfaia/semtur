@props(['title','subtitle'=>null,'image'=>null,'href'=>'#'])
<a href="{{ $href }}" class="block rounded-2xl overflow-hidden bg-white shadow-[0_3px_12px_rgba(0,0,0,0.06)]">
  <div class="aspect-[4/3] bg-slate-100">
    @if($image)<img src="{{ $image }}" class="w-full h-full object-cover" loading="lazy">@endif
  </div>
  <div class="p-3">
    <div class="text-[15px] font-semibold text-slate-900 line-clamp-1">{{ $title }}</div>
    @if($subtitle)<div class="text-[12px] text-slate-500 line-clamp-1 mt-0.5">{{ $subtitle }}</div>@endif
  </div>
</a>

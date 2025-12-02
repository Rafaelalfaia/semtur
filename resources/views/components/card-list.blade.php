@props(['title','subtitle'=>null,'image'=>null,'logo'=>null,'count'=>null,'href'=>'#'])
<a href="{{ $href }}" class="block rounded-2xl bg-white shadow border border-slate-100 overflow-hidden">
  <div class="p-3 flex gap-3">
    <div class="w-24 h-24 rounded-2xl overflow-hidden bg-slate-100 shrink-0">
      @if($image)<img src="{{ $image }}" class="w-full h-full object-cover" loading="lazy">@endif
    </div>
    <div class="flex-1 min-w-0">
      <div class="font-semibold line-clamp-1">{{ $title }}</div>
      @if($subtitle)
        <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-1">
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.3 7 13 7 13s7-7.7 7-13c0-3.9-3.1-7-7-7zM12 11.5c-1.4 0-2.5-1.1-2.5-2.5S10.6 6.5 12 6.5s2.5 1.1 2.5 2.5S13.4 11.5 12 11.5z"/></svg>
          {{ $subtitle }}
          @if($count)
            <span class="ml-2 inline-flex items-center text-[10px] px-1.5 py-0.5 rounded-full bg-slate-100">{{ $count }}+</span>
          @endif
        </div>
      @endif
      @if($logo)
        <img src="{{ $logo }}" class="h-5 mt-2" alt="Marca">
      @endif
    </div>
  </div>
</a>

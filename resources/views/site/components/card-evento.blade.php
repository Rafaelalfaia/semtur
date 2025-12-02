@props(['evento','edicao'])

<a href="{{ route('eventos.show', [$evento->slug, $edicao?->ano]) }}"
   class="block rounded-2xl bg-white shadow-sm hover:shadow-md transition overflow-hidden">

  <div class="relative h-36 w-full">
    @php
      $img = $evento->perfil_path ?? $evento->capa_path;
      $url = $img ? Storage::disk('public')->url($img) : asset('imagens/placeholder.jpg');
    @endphp
    <img src="{{ $url }}" class="h-full w-full object-cover" alt="{{ $evento->nome }}">

    @if($edicao)
      <span class="absolute left-2 top-2 rounded-full bg-black/60 text-white text-xs px-2 py-0.5">
        {{ $edicao->periodo ?: $edicao->ano }}
      </span>
    @endif
  </div>

  <div class="p-3">
    <div class="font-medium">{{ $evento->nome }}</div>
    <div class="text-xs text-gray-500 flex items-center gap-1">
      <svg width="14" height="14" fill="none" class="text-gray-400"><circle cx="7" cy="7" r="6" stroke="currentColor"/></svg>
      {{ $evento->cidade ?? 'Altamira' }}
    </div>
  </div>
</a>

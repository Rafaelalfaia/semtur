@props(['items' => []])

@if(!empty($items))
    <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
        <ol class="flex flex-wrap items-center gap-2">
            @foreach($items as $index => $item)
                <li class="inline-flex items-center gap-2">
                    @if(!empty($item['href']))
                        <a href="{{ $item['href'] }}" class="hover:text-emerald-700 transition">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="text-slate-700 font-medium">{{ $item['label'] }}</span>
                    @endif

                    @if(!$loop->last)
                        <span class="text-slate-300">/</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif

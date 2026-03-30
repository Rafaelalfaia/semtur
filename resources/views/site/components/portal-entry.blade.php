@props([
    'title',
    'text' => null,
    'href' => '#',
    'label' => 'Abrir',
])

<a href="{{ $href }}"
   class="group block rounded-3xl border border-slate-200 bg-white p-5 md:p-6 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-slate-900 group-hover:text-emerald-700 transition">
                {{ $title }}
            </h3>

            @if($text)
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    {{ $text }}
                </p>
            @endif
        </div>

        <div class="shrink-0 w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 grid place-items-center">
            &rarr;
        </div>
    </div>

    <div class="mt-5 inline-flex items-center text-sm font-medium text-emerald-700">
        {{ $label }}
    </div>
</a>

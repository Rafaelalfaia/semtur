@props([
    'title',
    'text' => null,
    'href' => '#',
    'label' => 'Abrir',
])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'group block rounded-3xl border border-slate-200 bg-white p-5 md:p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md']) }}
>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">
                {{ $title }}
            </h3>

            @if($text)
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ $text }}
                </p>
            @endif
        </div>

        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 transition group-hover:bg-emerald-100">
            <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                <path d="M7 4.5h8.5V13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="m5.5 14.5 10-10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
            </svg>
        </span>
    </div>

    <div class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-emerald-700">
        <span>{{ $label }}</span>
        <span aria-hidden="true" class="transition group-hover:translate-x-0.5">→</span>
    </div>
</a>

@extends('site.layouts.app')

@section('title', $page['title'] ?? 'VisitAltamira')
@section('meta.description', $page['description'] ?? 'Portal turístico de Altamira.')
@section('meta.image', '/imagens/altamira.jpg')

@section('site.content')
    <section class="bg-gradient-to-b from-emerald-50 via-white to-white border-b border-emerald-100">
        <div class="mx-auto w-full max-w-[1200px] px-4 md:px-6 py-8 md:py-12">
            @include('site.partials._breadcrumbs', [
                'items' => [
                    ['label' => 'Início', 'href' => route('site.home')],
                    ['label' => $page['title'] ?? 'Seção'],
                ],
            ])

            <div class="max-w-3xl mt-4">
                @if(!empty($page['eyebrow']))
                    <div class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-800 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]">
                        {{ $page['eyebrow'] }}
                    </div>
                @endif

                <h1 class="mt-4 text-3xl md:text-5xl font-bold tracking-tight text-slate-900">
                    {{ $page['title'] ?? 'Seção pública' }}
                </h1>

                @if(!empty($page['lead']))
                    <p class="mt-4 text-base md:text-lg leading-8 text-slate-600">
                        {{ $page['lead'] }}
                    </p>
                @endif

                @if(!empty($page['cta_href']) && !empty($page['cta_label']))
                    <div class="mt-6">
                        <a href="{{ $page['cta_href'] }}"
                           class="inline-flex items-center rounded-xl bg-emerald-600 hover:bg-emerald-700 px-5 py-3 text-white font-medium transition">
                            {{ $page['cta_label'] }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="py-10 md:py-14">
        <div class="mx-auto w-full max-w-[1200px] px-4 md:px-6">
            <div class="flex items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-xl md:text-2xl font-semibold text-slate-900">Estrutura inicial da seção</h2>
                    <p class="text-sm md:text-base text-slate-500 mt-1">
                        Esta página já funciona como ponto de entrada oficial da arquitetura pública.
                    </p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach(($page['cards'] ?? []) as $card)
                    <x-site.portal-entry
                        :title="$card['title'] ?? ''"
                        :text="$card['text'] ?? null"
                        :href="$card['href'] ?? '#'"
                        :label="$card['label'] ?? 'Abrir'"
                    />
                @endforeach
            </div>

            <div class="mt-10 rounded-3xl border border-slate-200 bg-slate-50 p-6 md:p-8">
                <h3 class="text-lg font-semibold text-slate-900">Próximo passo desta seção</h3>
                <p class="mt-2 text-slate-600 leading-7">
                    Nesta fase, a página entra como esqueleto funcional com SEO, rota, navegação, breadcrumbs
                    e padrão visual reutilizável. O conteúdo final será conectado aos módulos específicos nas próximas etapas.
                </p>
            </div>
        </div>
    </section>
@endsection

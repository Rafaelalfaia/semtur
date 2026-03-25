@extends('site.layouts.app')

@section('title', 'Jogos Indígenas')
@section('meta.description', 'Acompanhe a área pública dos Jogos Indígenas no portal Visit Altamira.')

@section('site.content')
<section class="bg-gradient-to-b from-[#F4FBF9] to-white py-10 md:py-16">
    <div class="mx-auto w-full max-w-[420px] px-4 md:max-w-[1024px] md:px-6 lg:max-w-[1200px]">
        <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-600 to-emerald-700 px-6 py-8 text-white md:px-10">
                <div class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-100/90">Portal público</div>
                <h1 class="mt-3 text-3xl font-semibold tracking-[-0.03em] md:text-4xl">Jogos Indígenas</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-emerald-50/90 md:text-base">
                    Esta área pública foi reservada para a próxima etapa do módulo. Em breve, o portal exibirá o jogo principal, as edições e seus conteúdos relacionados.
                </p>
            </div>

            <div class="px-6 py-8 md:px-10 md:py-10">
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-5">
                        <div class="text-sm font-semibold text-slate-900">Base pública preparada</div>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            O atalho da home já aponta para a rota oficial do módulo, preservando a navegação pública e a expansão futura da seção.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-5">
                        <div class="text-sm font-semibold text-slate-900">Próxima entrega</div>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            A fase seguinte vai conectar esta área ao conteúdo editorial do módulo, com a vitrine pública dos Jogos Indígenas.
                        </p>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="{{ route('site.home') }}" class="inline-flex items-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-800 transition hover:border-emerald-300 hover:text-emerald-700">
                        Voltar para a home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

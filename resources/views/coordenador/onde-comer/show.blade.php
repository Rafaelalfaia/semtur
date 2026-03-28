@extends('site.layouts.app')

@section('title', ($pagina->titulo ?? 'Onde comer') . ' • Visit Altamira')

@section('site.content')
<section class="py-10">
    <div class="mx-auto max-w-6xl px-4">
        <h1 class="text-3xl font-bold text-slate-900">
            {{ $pagina->titulo ?? 'Onde comer em Altamira' }}
        </h1>

        @if(!empty($pagina->resumo))
            <p class="mt-4 text-slate-600">
                {{ $pagina->resumo }}
            </p>
        @endif

        <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6">
            <p class="text-slate-700">
                Blade carregado com sucesso.
            </p>
        </div>
    </div>
</section>
@endsection

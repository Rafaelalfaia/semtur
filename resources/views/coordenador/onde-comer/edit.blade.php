@extends('console.layout')

@section('title', 'Onde comer')
@section('page.title', 'Onde comer')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-100">Página Onde comer</h1>
            <p class="mt-1 text-sm text-slate-400">
                Edite os textos editoriais e escolha manualmente as empresas gastronômicas que vão aparecer no site.
            </p>
        </div>

        @if(($pagina->status ?? null) === 'publicado')
            <a
                href="{{ route('site.onde_comer') }}"
                target="_blank"
                class="inline-flex items-center justify-center rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-2.5 text-emerald-200 hover:bg-emerald-500/15"
            >
                Ver no site
            </a>
        @endif
    </div>

    @include('coordenador.partials.flash')

    <form
        method="POST"
        action="{{ route('coordenador.onde_comer.update') }}"
        enctype="multipart/form-data"
        class="space-y-6"
    >
        @csrf
        @method('PUT')

        @include('coordenador.onde-comer._form')

        <div class="flex flex-wrap items-center gap-3 border-t border-white/10 pt-5">
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 font-medium text-white hover:bg-emerald-700"
            >
                Salvar página
            </button>
        </div>
    </form>
</div>
@endsection

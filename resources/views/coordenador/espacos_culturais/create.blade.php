@extends('console.layout')

@section('title', 'Novo espaço cultural')
@section('page.title', 'Novo espaço cultural')

@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-rose-500/30 bg-rose-500/10 p-3 text-rose-200">
            <strong>Corrija os campos abaixo:</strong>
            <ul class="list-disc list-inside mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('coordenador.espacos-culturais.store') }}" class="space-y-6">
        @csrf

        @include('coordenador.espacos_culturais._form', [
            'espaco' => $espaco,
            'diasSemana' => $diasSemana,
        ])

        <div class="flex justify-end gap-2">
            <a href="{{ route('coordenador.espacos-culturais.index') }}"
               class="px-4 py-2 rounded-lg bg-white/5 text-slate-200 hover:bg-white/10">
                Cancelar
            </a>

            <button class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white">
                Salvar
            </button>
        </div>
    </form>
@endsection

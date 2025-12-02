@extends('console.layout')
@section('title', 'Editar ponto turístico')

@section('content')
  <div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold text-slate-100">Editar ponto turístico</h1>
      <a href="{{ route('coordenador.pontos.index') }}"
         class="inline-flex items-center px-3 py-2 rounded-lg border border-white/10 text-slate-100 hover:bg-white/10">
        Voltar
      </a>
    </div>

    @if(session('ok'))
      <div class="mb-4 rounded-lg bg-emerald-600/15 border border-emerald-500/30 px-3 py-2 text-emerald-200">
        {{ session('ok') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-4 rounded-lg bg-rose-600/15 border border-rose-500/30 px-3 py-2 text-rose-200">
        <strong>Ops!</strong> Verifique os campos abaixo.
      </div>
    @endif

    <form method="POST"
          action="{{ route('coordenador.pontos.update', $ponto) }}"
          enctype="multipart/form-data"
          class="space-y-6">
      @csrf
      @method('PUT')

      {{-- Reaproveita exatamente o mesmo partial do create --}}
      @include('coordenador.pontos._form', ['ponto' => $ponto, 'isEdit' => true])

      <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="inline-flex items-center px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-500 text-white">
          Salvar alterações
        </button>

        <a href="{{ route('coordenador.pontos.index') }}"
           class="inline-flex items-center px-4 py-2 rounded-lg border border-white/10 text-slate-100 hover:bg-white/10">
          Cancelar
        </a>
      </div>
    </form>
  </div>
@endsection

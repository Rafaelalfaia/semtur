@extends('console.layout')
@section('title','Editar categoria')

@section('content')
  <h1 class="text-xl font-semibold mb-4 text-slate-100">Editar categoria</h1>

  @if (session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 p-3 text-emerald-200">
      {{ session('ok') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="mb-4 rounded-lg border border-rose-500/30 bg-rose-500/10 p-3 text-rose-200">
      <strong>Corrija os campos:</strong>
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.categorias.update', $categoria) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @include('coordenador.categorias._form', [
      'categoria' => $categoria,
      'isCreate'  => false,
    ])

    <div class="mt-6 flex gap-2">
      <button type="submit" class="rounded-lg bg-slate-200/10 px-4 py-2 text-slate-50 hover:bg-slate-200/20">
        Salvar alterações
      </button>
    </div>
  </form>
@endsection

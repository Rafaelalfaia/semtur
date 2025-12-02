@extends('console.layout')
@section('title','Nova categoria')

@section('content')
  <h1 class="text-xl font-semibold mb-4 text-slate-100">Nova categoria</h1>

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

  <form method="POST" action="{{ route('coordenador.categorias.store') }}" enctype="multipart/form-data">
    @csrf

    @include('coordenador.categorias._form', [
      'categoria' => new \App\Models\Catalogo\Categoria(),
      'isCreate'  => true,
    ])

    <div class="mt-6">
      <button type="submit" class="rounded-lg bg-emerald-500/90 px-4 py-2 font-semibold text-emerald-950 hover:bg-emerald-400">
        Salvar
      </button>
    </div>
  </form>
@endsection

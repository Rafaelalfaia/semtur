@extends('console.layout')
@section('title','Técnicos')
@section('page.title','Técnicos')

@section('content')
<div class="mx-auto w-full max-w-[1100px] px-4 md:px-6 py-6 md:py-10">

  <div class="flex flex-col sm:flex-row sm:items-end gap-3 justify-between mb-5">
    <form method="GET" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
      <div>
        <label class="block text-xs text-slate-400 mb-1">Buscar</label>
        <input type="text" name="q" value="{{ $q ?? '' }}"
               class="w-full sm:w-[280px] rounded-lg bg-white/5 border border-white/10 px-3 py-2"
               placeholder="Nome, e-mail ou CPF">
      </div>
      <div class="sm:self-end">
        <button class="rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2">Filtrar</button>
      </div>
    </form>

    <a href="{{ route('coordenador.tecnicos.create') }}"
       class="inline-flex items-center justify-center rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-white">
      + Novo Técnico
    </a>
  </div>

  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-emerald-200">
      {{ session('ok') }}
    </div>
  @endif

  <div class="overflow-x-auto rounded-xl border border-white/10">
    <table class="min-w-full text-sm">
      <thead class="bg-white/5 text-slate-300">
        <tr>
          <th class="text-left font-medium px-4 py-3">Nome</th>
          <th class="text-left font-medium px-4 py-3">CPF</th>
          <th class="text-left font-medium px-4 py-3">E-mail</th>
          <th class="text-left font-medium px-4 py-3">Criado em</th>
          <th class="text-right font-medium px-4 py-3">Ações</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-white/10">
        @forelse($users as $u)
          <tr class="hover:bg-white/5">
            <td class="px-4 py-3">{{ $u->name }}</td>
            <td class="px-4 py-3">{{ $u->cpf ?: '—' }}</td>
            <td class="px-4 py-3">{{ $u->email ?: '—' }}</td>
            <td class="px-4 py-3">{{ optional($u->created_at)->format('d/m/Y H:i') }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2">
                <a href="{{ route('coordenador.tecnicos.edit',$u) }}"
                   class="rounded-md border border-white/10 bg-white/5 hover:bg-white/10 px-3 py-1.5">Editar</a>
                <form method="POST" action="{{ route('coordenador.tecnicos.destroy',$u) }}"
                      onsubmit="return confirm('Excluir este técnico?');">
                  @csrf @method('DELETE')
                  <button class="rounded-md border border-red-500/30 bg-red-500/10 hover:bg-red-500/20 px-3 py-1.5">
                    Excluir
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-6 text-center text-slate-400">Nenhum técnico encontrado.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection

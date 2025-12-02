@extends('console.admin-layout')
@section('title','Usuários')
@section('page.title','Usuários')

@section('content')
<div class="mx-auto w-full max-w-[1200px] px-4 md:px-6 py-6 md:py-10">

  {{-- Toolbar / Filtros --}}
  <div class="flex flex-col sm:flex-row sm:items-end gap-3 justify-between mb-5">
    <form method="GET" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
      <div>
        <label class="block text-xs text-slate-400 mb-1">Buscar</label>
        <input type="text" name="q" value="{{ $q ?? '' }}"
               class="w-full sm:w-[260px] rounded-lg bg-white/5 border border-white/10 px-3 py-2"
               placeholder="Nome, e-mail ou CPF">
      </div>

      <div>
        <label class="block text-xs text-slate-400 mb-1">Papel</label>
        <select name="role" class="w-full sm:w-[220px] rounded-lg bg-white/5 border border-white/10 px-3 py-2">
          <option value="">Todos</option>
          @foreach($roles as $id => $name)
            <option value="{{ $name }}" @selected(($role ?? '')===$name)>{{ $name }}</option>
          @endforeach
        </select>
      </div>

      <div class="sm:self-end">
        <button class="rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2">Filtrar</button>
      </div>
    </form>

    <a href="{{ route('admin.usuarios.create') }}"
       class="inline-flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 px-4 py-2">
      + Novo Usuário
    </a>
  </div>

  {{-- Feedback --}}
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-emerald-200">
      {{ session('ok') }}
    </div>
  @endif
  @if(session('erro'))
    <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2 text-red-200">
      {{ session('erro') }}
    </div>
  @endif

  {{-- Tabela --}}
  <div class="overflow-x-auto rounded-xl border border-white/10">
    <table class="min-w-full text-sm">
      <thead class="bg-white/5 text-slate-300">
        <tr>
          <th class="text-left font-medium px-4 py-3">Nome</th>
          <th class="text-left font-medium px-4 py-3">CPF</th>
          <th class="text-left font-medium px-4 py-3">E-mail</th>
          <th class="text-left font-medium px-4 py-3">Papéis</th>
          <th class="text-left font-medium px-4 py-3">Criado em</th>
          <th class="text-right font-medium px-4 py-3">Ações</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-white/10">
        @forelse($users as $u)
          <tr class="hover:bg-white/5">
            <td class="px-4 py-3">{{ $u->name }}</td>
            <td class="px-4 py-3">{{ $u->cpf }}</td>
            <td class="px-4 py-3">{{ $u->email ?? '—' }}</td>
            <td class="px-4 py-3">
              @php $rnames = $u->roles->pluck('name')->all(); @endphp
              @if($rnames)
                <div class="flex flex-wrap gap-1">
                  @foreach($rnames as $rname)
                    <span class="rounded-full bg-white/10 px-2 py-[2px] text-xs">{{ $rname }}</span>
                  @endforeach
                </div>
              @else
                <span class="text-slate-400">—</span>
              @endif
            </td>
            <td class="px-4 py-3">{{ optional($u->created_at)->format('d/m/Y H:i') }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2">
                <a href="{{ route('admin.usuarios.edit',$u) }}"
                   class="rounded-md border border-white/10 bg-white/5 hover:bg-white/10 px-3 py-1.5">Editar</a>
                <form method="POST" action="{{ route('admin.usuarios.destroy',$u) }}"
                      onsubmit="return confirm('Excluir este usuário?');">
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
            <td colspan="6" class="px-4 py-6 text-center text-slate-400">Nenhum usuário encontrado.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $users->links() }}
  </div>
</div>
@endsection

@extends('console.layout')
@section('title','Novo Técnico')
@section('page.title','Novo Técnico')

@section('content')
@php
  $labelGroup = fn($g)=> [
    'categorias'=>'Categorias','empresas'=>'Empresas','pontos'=>'Pontos Turísticos',
    'banners'=>'Banners','banners_destaque'=>'Banners de Destaque',
    'avisos'=>'Avisos','eventos'=>'Eventos','secretaria'=>'Secretaria','equipe'=>'Equipe',
    'relatorios'=>'Relatórios'
  ][$g] ?? ucfirst($g);

  $labelAction = fn($a)=> [
    'view'=>'Visualizar','create'=>'Criar','update'=>'Editar','delete'=>'Excluir',
    'publicar'=>'Publicar','arquivar'=>'Arquivar','rascunho'=>'Marcar como rascunho',
    'manage'=>'Gerenciar','reordenar'=>'Reordenar','toggle'=>'Ativar/Desativar',
    'edicoes.manage'=>'Gerenciar Edições','atrativos.manage'=>'Gerenciar Atrativos',
    'atrativos.reordenar'=>'Reordenar Atrativos','midias.manage'=>'Gerenciar Mídias',
    'midias.reordenar'=>'Reordenar Mídias'
  ][$a] ?? ucfirst(str_replace('.',' ', $a));
@endphp

<div class="mx-auto w-full max-w-[900px] px-4 md:px-6 py-6 md:py-10 space-y-6">

  @if ($errors->any())
    <div class="rounded-lg border border-rose-500/40 bg-rose-500/10 p-3 text-rose-200">
      <div class="font-semibold mb-2">Corrija os campos abaixo:</div>
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.tecnicos.store') }}" class="space-y-6">
    @csrf

    <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4 grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm text-slate-300 mb-1">Nome*</label>
        <input type="text" name="name" value="{{ old('name') }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm text-slate-300 mb-1">E-mail</label>
        <input type="email" name="email" value="{{ old('email') }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm text-slate-300 mb-1">CPF</label>
        <input type="text" name="cpf" value="{{ old('cpf') }}"
               placeholder="00000000000"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2">
        <p class="text-xs text-slate-400 mt-1">Opcional. Apenas dígitos.</p>
      </div>
      <div></div>
      <div>
        <label class="block text-sm text-slate-300 mb-1">Senha*</label>
        <input type="password" name="password"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm text-slate-300 mb-1">Confirmar senha*</label>
        <input type="password" name="password_confirmation"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2">
      </div>
    </div>

    <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold">Permissões do técnico</h2>
        <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
          <input id="sel-all" type="checkbox" class="h-4 w-4"> Selecionar todas
        </label>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        @foreach($permissions as $group => $perms)
          <div class="rounded-lg border border-white/10 p-3">
            <div class="flex items-center justify-between mb-2">
              <div class="font-semibold">{{ $labelGroup($group) }}</div>
              <label class="text-xs inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" class="h-4 w-4 sel-group" data-group="{{ $group }}"> marcar grupo
              </label>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              @foreach($perms as $p)
                @php
                  $action = \Illuminate\Support\Str::after($p->name, $group.'.');
                @endphp
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" name="perms[]" value="{{ $p->name }}" class="h-4 w-4 cb-{{ $group }}">
                  {{ $labelAction($action) }}
                </label>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div>
      <button class="rounded-lg bg-emerald-600 hover:bg-emerald-500 px-5 py-2.5 font-semibold">Salvar</button>
      <a href="{{ route('coordenador.tecnicos.index') }}" class="ml-2 px-4 py-2 rounded-lg border border-white/10">Cancelar</a>
    </div>
  </form>
</div>

@push('scripts')
<script>
  const all = document.getElementById('sel-all');
  if (all) all.addEventListener('change', e => {
    document.querySelectorAll('input[type=checkbox][name="perms[]"]').forEach(cb => cb.checked = all.checked);
    document.querySelectorAll('.sel-group').forEach(g => g.checked = all.checked);
  });
  document.querySelectorAll('.sel-group').forEach(g => {
    g.addEventListener('change', () => {
      const grp = g.dataset.group;
      document.querySelectorAll('.cb-'+grp).forEach(cb => cb.checked = g.checked);
    });
  });
</script>
@endpush
@endsection

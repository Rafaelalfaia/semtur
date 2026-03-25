@extends('console.layout')
@section('title','Novo Tecnico')
@section('page.title','Novo Tecnico')
@section('topbar.description', 'Cadastro de tecnico com dados principais e permissoes vinculadas ao padrao visual do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.tecnicos.index') }}" class="ui-console-topbar-tab">Tecnicos</a>
  <span class="ui-console-topbar-tab is-active">Novo tecnico</span>
@endsection

@section('content')
@php
  $labelGroup = fn($g)=> [
    'categorias'=>'Categorias','empresas'=>'Empresas','pontos'=>'Pontos Turisticos',
    'banners'=>'Banners','banners_destaque'=>'Banners de Destaque',
    'avisos'=>'Avisos','eventos'=>'Eventos','secretaria'=>'Secretaria','equipe'=>'Equipe',
    'relatorios'=>'Relatorios','espacos_culturais'=>'Museus e Teatros',
    'roteiros'=>'Roteiros','onde_comer'=>'Onde comer','onde_ficar'=>'Onde ficar',
    'guias'=>'Guias e Revistas','videos'=>'Videos','jogos_indigenas'=>'Jogos Indigenas',
    'rota_do_cacau'=>'Rota do Cacau'
  ][$g] ?? ucfirst($g);

  $labelAction = fn($a)=> [
    'view'=>'Visualizar','create'=>'Criar','update'=>'Editar','delete'=>'Excluir',
    'publicar'=>'Publicar','arquivar'=>'Arquivar','rascunho'=>'Marcar como rascunho',
    'manage'=>'Gerenciar','reordenar'=>'Reordenar','toggle'=>'Ativar/Desativar',
    'edicoes.manage'=>'Gerenciar Edicoes','atrativos.manage'=>'Gerenciar Atrativos',
    'atrativos.reordenar'=>'Reordenar Atrativos','midias.manage'=>'Gerenciar Midias',
    'midias.reordenar'=>'Reordenar Midias',
    'edicoes.view'=>'Visualizar Edicoes','edicoes.create'=>'Criar Edicoes',
    'edicoes.update'=>'Editar Edicoes','edicoes.delete'=>'Excluir Edicoes',
    'edicoes.publicar'=>'Publicar Edicoes','edicoes.arquivar'=>'Arquivar Edicoes',
    'edicoes.rascunho'=>'Edicoes em Rascunho',
    'edicoes.fotos.view'=>'Visualizar Fotos','edicoes.fotos.create'=>'Adicionar Fotos',
    'edicoes.fotos.update'=>'Editar Fotos','edicoes.fotos.delete'=>'Excluir Fotos',
    'edicoes.videos.view'=>'Visualizar Videos','edicoes.videos.create'=>'Adicionar Videos',
    'edicoes.videos.update'=>'Editar Videos','edicoes.videos.delete'=>'Excluir Videos',
    'edicoes.patrocinadores.view'=>'Visualizar Patrocinadores',
    'edicoes.patrocinadores.create'=>'Adicionar Patrocinadores',
    'edicoes.patrocinadores.update'=>'Editar Patrocinadores',
    'edicoes.patrocinadores.delete'=>'Excluir Patrocinadores'
  ][$a] ?? ucfirst(str_replace('.',' ', $a));
@endphp

<div class="ui-console-page">
  @if ($errors->any())
    <div class="ui-alert ui-alert-danger mb-4">
      <div class="font-semibold mb-2">Corrija os campos abaixo:</div>
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <x-dashboard.page-header
    title="Novo tecnico"
    subtitle="Crie uma conta tecnica com dados essenciais e permissões refinadas, mantendo o shell compartilhado."
  />

  <form method="POST" action="{{ route('coordenador.tecnicos.store') }}" class="mt-5 space-y-5">
    @csrf

    <x-dashboard.section-card title="Dados do tecnico" subtitle="Identificacao e acesso" class="ui-coord-dashboard-panel">
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="ui-form-label">Nome*</label>
          <input type="text" name="name" value="{{ old('name') }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">E-mail</label>
          <input type="email" name="email" value="{{ old('email') }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">CPF</label>
          <input type="text" name="cpf" value="{{ old('cpf') }}" placeholder="00000000000" class="ui-form-control">
          <p class="ui-profile-help">Opcional. Apenas digitos.</p>
        </div>
        <div></div>
        <div>
          <label class="ui-form-label">Senha*</label>
          <input type="password" name="password" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Confirmar senha*</label>
          <input type="password" name="password_confirmation" class="ui-form-control">
        </div>
      </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Permissoes do tecnico" subtitle="Somente permissoes que voce ja possui podem ser delegadas ao tecnico" class="ui-coord-dashboard-panel">
      <div class="mb-4 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] px-4 py-3 text-sm text-[var(--ui-text-soft)]">
        O Tecnico recebe apenas um subconjunto operacional das suas permissoes atuais. Itens sensiveis de usuarios, tecnicos e manutencao nao aparecem aqui.
      </div>
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-[var(--ui-text-title)]">Permissoes</h2>
        <label class="inline-flex items-center gap-2 text-sm cursor-pointer text-[var(--ui-text-soft)]">
          <input id="sel-all" type="checkbox" class="ui-form-check h-4 w-4"> Selecionar todas
        </label>
      </div>

      <div class="ui-tech-permission-grid">
        @forelse($permissions as $group => $perms)
          <div class="ui-tech-permission-card">
            <div class="flex items-center justify-between mb-2">
              <div class="font-semibold text-[var(--ui-text-title)]">{{ $labelGroup($group) }}</div>
              <label class="text-xs inline-flex items-center gap-2 cursor-pointer text-[var(--ui-text-soft)]">
                <input type="checkbox" class="ui-form-check h-4 w-4 sel-group" data-group="{{ $group }}"> marcar grupo
              </label>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              @foreach($perms as $p)
                @php $action = \Illuminate\Support\Str::after($p->name, $group.'.'); @endphp
                <label class="inline-flex items-center gap-2 text-sm">
                  <input type="checkbox" name="perms[]" value="{{ $p->name }}" class="ui-form-check h-4 w-4 cb-{{ $group }}">
                  {{ $labelAction($action) }}
                </label>
              @endforeach
            </div>
          </div>
        @empty
          <div class="rounded-2xl border border-dashed border-[var(--ui-border)] px-4 py-6 text-sm text-[var(--ui-text-soft)]">
            Nenhuma permissao delegavel encontrada no momento. Peça ao Admin para revisar suas permissoes de Coordenador.
          </div>
        @endforelse
      </div>
    </x-dashboard.section-card>

    <div class="flex items-center gap-3">
      <button class="ui-btn-primary">Salvar</button>
      <a href="{{ route('coordenador.tecnicos.index') }}" class="ui-btn-secondary">Cancelar</a>
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

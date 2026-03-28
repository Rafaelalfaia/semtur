@extends('console.layout')
@section('title','Editar Técnico')
@section('page.title','Editar Técnico')
@section('topbar.description', 'Atualize dados de acesso e permissões do técnico sem sair do shell compartilhado do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.tecnicos.index') }}" class="ui-console-topbar-tab">Técnicos</a>
  <span class="ui-console-topbar-tab is-active">Editar técnico</span>
@endsection

@section('content')
@php
  $labelGroup = fn($g)=> [
    'categorias'=>'Categorias','empresas'=>'Empresas','pontos'=>'Pontos Turísticos',
    'banners'=>'Banners','banners_destaque'=>'Banners de Destaque',
    'avisos'=>'Avisos','eventos'=>'Eventos','secretaria'=>'Secretaria','equipe'=>'Equipe',
    'relatorios'=>'Relatórios','espacos_culturais'=>'Museus e Teatros',
    'roteiros'=>'Roteiros','onde_comer'=>'Onde comer','onde_ficar'=>'Onde ficar',
    'guias'=>'Guias e Revistas','videos'=>'Vídeos','jogos_indigenas'=>'Jogos Indígenas',
    'rota_do_cacau'=>'Rota do Cacau'
  ][$g] ?? ucfirst($g);

  $labelAction = fn($a)=> [
    'view'=>'Visualizar','create'=>'Criar','update'=>'Editar','delete'=>'Excluir',
    'publicar'=>'Publicar','arquivar'=>'Arquivar','rascunho'=>'Marcar como rascunho',
    'manage'=>'Gerenciar','reordenar'=>'Reordenar','toggle'=>'Ativar/Desativar',
    'edicoes.manage'=>'Gerenciar Edições','atrativos.manage'=>'Gerenciar Atrativos',
    'atrativos.reordenar'=>'Reordenar Atrativos','midias.manage'=>'Gerenciar Mídias',
    'midias.reordenar'=>'Reordenar Mídias',
    'edicoes.view'=>'Visualizar Edições','edicoes.create'=>'Criar Edições',
    'edicoes.update'=>'Editar Edições','edicoes.delete'=>'Excluir Edições',
    'edicoes.publicar'=>'Publicar Edições','edicoes.arquivar'=>'Arquivar Edições',
    'edicoes.rascunho'=>'Edições em Rascunho',
    'edicoes.fotos.view'=>'Visualizar Fotos','edicoes.fotos.create'=>'Adicionar Fotos',
    'edicoes.fotos.update'=>'Editar Fotos','edicoes.fotos.delete'=>'Excluir Fotos',
    'edicoes.videos.view'=>'Visualizar Vídeos','edicoes.videos.create'=>'Adicionar Vídeos',
    'edicoes.videos.update'=>'Editar Vídeos','edicoes.videos.delete'=>'Excluir Vídeos',
    'edicoes.patrocinadores.view'=>'Visualizar Patrocinadores',
    'edicoes.patrocinadores.create'=>'Adicionar Patrocinadores',
    'edicoes.patrocinadores.update'=>'Editar Patrocinadores',
    'edicoes.patrocinadores.delete'=>'Excluir Patrocinadores'
  ][$a] ?? ucfirst(str_replace('.',' ', $a));
@endphp

<div class="ui-console-page">
  @if(session('ok'))
    <div class="ui-alert ui-alert-success mb-4">{{ session('ok') }}</div>
  @endif

  @if ($errors->any())
    <div class="ui-alert ui-alert-danger mb-4">
      <div class="font-semibold mb-2">Corrija os campos abaixo:</div>
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <x-dashboard.page-header
    title="Editar técnico"
    subtitle="Revise dados principais e refinamento de permissões mantendo coerência com o console do Admin e Coordenador."
  />

  <form method="POST" action="{{ route('coordenador.tecnicos.update', $usuario) }}" class="mt-5 space-y-5">
    @csrf
    @method('PUT')

    <x-dashboard.section-card title="Dados do técnico" subtitle="Informações, contato e senha opcional" class="ui-coord-dashboard-panel">
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="ui-form-label">Nome*</label>
          <input type="text" name="name" value="{{ old('name', $usuario->name) }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">E-mail</label>
          <input type="email" name="email" value="{{ old('email', $usuario->email) }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">CPF</label>
          <input type="text" name="cpf" value="{{ old('cpf', $usuario->cpf) }}" class="ui-form-control">
          <p class="ui-profile-help">Opcional. Apenas digitos.</p>
        </div>
        <div></div>
        <div>
          <label class="ui-form-label">Nova senha</label>
          <input type="password" name="password" class="ui-form-control">
          <p class="ui-profile-help">Preencha somente se quiser redefinir o acesso.</p>
        </div>
        <div>
          <label class="ui-form-label">Confirmar nova senha</label>
          <input type="password" name="password_confirmation" class="ui-form-control">
        </div>
      </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Permissões do técnico" subtitle="Somente permissões que você ainda possui podem continuar delegadas" class="ui-coord-dashboard-panel">
      <div class="mb-4 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] px-4 py-3 text-sm text-[var(--ui-text-soft)]">
        Se alguma permissão sair do seu conjunto de Coordenador, ela deixa de ser delegável e será removida do Técnico na próxima sincronização.
      </div>
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-[var(--ui-text-title)]">Permissões</h2>
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
                @php
                  $action = \Illuminate\Support\Str::after($p->name, $group.'.');
                  $checked = in_array($p->name, $usuarioPerms ?? []);
                @endphp
                <label class="inline-flex items-center gap-2 text-sm">
                  <input type="checkbox" name="perms[]" value="{{ $p->name }}" class="ui-form-check h-4 w-4 cb-{{ $group }}" @checked($checked)>
                  {{ $labelAction($action) }}
                </label>
              @endforeach
            </div>
          </div>
        @empty
          <div class="rounded-2xl border border-dashed border-[var(--ui-border)] px-4 py-6 text-sm text-[var(--ui-text-soft)]">
            Nenhuma permissão delegável encontrada no momento. Revise suas permissões de Coordenador com o Admin antes de atualizar este Técnico.
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

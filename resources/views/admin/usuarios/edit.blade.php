@extends('console.layout')

@section('title', 'Editar Usuário - '.($usuario->name ?? 'Usuário'))

@section('topbar.description', 'Edição administrativa de dados, papéis e permissões da conta selecionada.')

@section('topbar.nav')
    <a href="{{ route('admin.usuarios.index') }}" class="ui-console-topbar-tab">Usuários</a>
    <span class="ui-console-topbar-tab is-active">Editar</span>
@endsection

@section('content')
@php
    use Illuminate\Support\Str;
    $oldRoles = collect(old('roles', $usuarioRoles ?? []))->map(fn($v)=>(string)$v)->all();
    $oldPerms = collect(old('perms', $usuarioPerms ?? []))->all();

    $maskCpf = function($cpf){
        $d = preg_replace('/\D+/', '', (string)$cpf);
        return strlen($d)===11 ? substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2) : '';
    };
@endphp

<div class="ui-console-page">
    <x-dashboard.page-header
        title="Editar usuário"
        subtitle="Atualize dados, papéis e permissões sem sair do shell principal do console."
    >
        <x-slot:actions>
            <a href="{{ route('admin.usuarios.index') }}" class="ui-btn-secondary">
                Voltar
            </a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" class="mt-5 space-y-4">
        @csrf
        @method('PUT')

        <x-dashboard.section-card title="Dados básicos" subtitle="Informações principais da conta">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="ui-form-label" for="name">Nome</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $usuario->name) }}" class="ui-form-control" required>
                    @error('name')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="ui-form-label" for="email">E-mail (opcional)</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $usuario->email) }}" class="ui-form-control" placeholder="ex: pessoa@dominio.com">
                    @error('email')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="ui-form-label" for="cpf_mask">CPF (opcional)</label>
                    <input id="cpf_mask" type="text" inputmode="numeric" placeholder="000.000.000-00" value="{{ old('cpf', $maskCpf($usuario->cpf)) }}" class="ui-form-control">
                    <input id="cpf" name="cpf" type="hidden" value="{{ old('cpf', preg_replace('/\D+/','',$usuario->cpf ?? '')) }}">
                    @error('cpf')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-dashboard.section-card>

        <x-dashboard.section-card title="Acesso e segurança" subtitle="Atualize a senha somente quando necessário">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="ui-form-label" for="password">Nova senha (opcional)</label>
                    <input id="password" name="password" type="password" class="ui-form-control">
                    @error('password')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="ui-form-label" for="password_confirmation">Confirmar nova senha</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="ui-form-control">
                </div>
            </div>
        </x-dashboard.section-card>

        <x-dashboard.section-card title="Papéis" subtitle="Selecione um ou mais papéis">
            @php
                $roleItems = collect($roles)->map(function($r){
                    if (is_object($r)) return ['id'=>(string)$r->id, 'name'=>(string)$r->name];
                    $name = (string)$r;
                    return ['id'=>$name, 'name'=>$name];
                })->all();
            @endphp

            <select name="roles[]" multiple class="ui-form-select">
                @foreach($roleItems as $r)
                    @php $selected = in_array($r['id'],$oldRoles,true) || in_array($r['name'],$oldRoles,true); @endphp
                    <option value="{{ $r['id'] }}" @selected($selected)>{{ $r['name'] }}</option>
                @endforeach
            </select>
            @error('roles')<p class="ui-form-error">{{ $message }}</p>@enderror
        </x-dashboard.section-card>

        <x-dashboard.section-card title="Permissões (refino opcional)" subtitle="Ajuste granular de permissões quando necessário">
            @php
                $groupLabel = function(string $g){
                    $map = [
                        'avisos' => 'Avisos',
                        'banners' => 'Banners',
                        'banners_destaque' => 'Banners de Destaque',
                        'categorias' => 'Categorias',
                        'console' => 'Ferramentas do Console',
                        'empresas' => 'Empresas',
                        'equipe' => 'Equipe',
                        'eventos' => 'Eventos',
                        'pontos' => 'Pontos Turísticos',
                        'relatorios' => 'Relatórios',
                        'secretaria' => 'Página da Secretaria',
                        'usuarios' => 'Usuários',
                    ];
                    return $map[$g] ?? \Illuminate\Support\Str::headline($g);
                };

                $actionLabel = [
                    'view' => 'Visualizar',
                    'create' => 'Criar',
                    'update' => 'Editar',
                    'delete' => 'Excluir',
                    'publicar' => 'Publicar',
                    'arquivar' => 'Arquivar',
                    'rascunho' => 'Marcar como rascunho',
                    'manage' => 'Gerenciar',
                    'reordenar' => 'Reordenar',
                    'toggle' => 'Ativar/Desativar',
                    'clear' => 'Limpar',
                ];

                $resourceLabel = [
                    'atrativos' => 'Atrativos',
                    'edicoes' => 'Edições',
                    'midias' => 'Midias',
                    'cache' => 'Cache',
                ];

                $prettyPerm = function(string $full) use ($actionLabel, $resourceLabel){
                    if ($full === 'console.cache.clear') return 'Limpar cache do console';
                    $parts = explode('.', $full);
                    array_shift($parts);
                    if (count($parts) === 1) {
                        $a = $parts[0];
                        return $actionLabel[$a] ?? \Illuminate\Support\Str::headline($a);
                    }
                    [$sub, $a] = $parts;
                    $aLabel = $actionLabel[$a] ?? \Illuminate\Support\Str::headline($a);
                    $subLabel = $resourceLabel[$sub] ?? \Illuminate\Support\Str::headline($sub);
                    return "{$aLabel} {$subLabel}";
                };
            @endphp

            <div class="mb-4 flex items-center justify-between">
                <span class="text-sm text-[var(--ui-text-soft)]">Seleção global e por grupo</span>
                <label class="inline-flex items-center gap-2 text-xs text-[var(--ui-text-soft)]">
                    <input type="checkbox" id="perm_all_toggle" class="ui-form-check rounded">
                    Selecionar todas
                </label>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach($permissions as $group => $perms)
                    <fieldset class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-input-bg)] p-3">
                        <legend class="mb-2 flex items-center justify-between text-xs font-semibold">
                            <span>{{ $groupLabel($group) }}</span>
                            <label class="inline-flex items-center gap-1 text-[11px] text-[var(--ui-text-soft)]">
                                <input type="checkbox" class="perm-group-toggle ui-form-check rounded" data-group="{{ $group }}">
                                marcar grupo
                            </label>
                        </legend>

                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach($perms as $perm)
                                @php $pid = 'perm_'.str_replace(['.',' '],['_','_'],$perm->name); @endphp
                                <label for="{{ $pid }}" class="inline-flex items-center gap-2">
                                    <input id="{{ $pid }}" type="checkbox" name="perms[]" value="{{ $perm->name }}" class="perm-chk ui-form-check rounded" data-group="{{ $group }}" @checked(in_array($perm->name,$oldPerms,true))>
                                    <span class="text-sm">{{ $prettyPerm($perm->name) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
            </div>

            @error('perms')<p class="ui-form-error">{{ $message }}</p>@enderror
        </x-dashboard.section-card>

        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('admin.usuarios.index') }}" class="ui-btn-secondary">Voltar</a>

            <div class="flex items-center gap-3">
                @if(auth()->id() !== $usuario->id)
                    <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}" onsubmit="return confirm('Excluir este usuário?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ui-btn-danger">
                            Excluir
                        </button>
                    </form>
                @else
                    <button class="ui-btn-secondary opacity-60" disabled>
                        Excluir
                    </button>
                @endif

                <button class="ui-btn-primary">Salvar alterações</button>
            </div>
        </div>
    </form>
</div>

<script>
  (function(){
    const mask = document.getElementById('cpf_mask');
    const hid  = document.getElementById('cpf');
    if (!mask || !hid) return;
    const only = s => (s||'').replace(/\D+/g,'');
    const mcpf = d => {
      const a = only(d).slice(0,11);
      let o=''; for (let i=0;i<a.length;i++){ o+=a[i]; if(i===2||i===5) o+='.'; if(i===8) o+='-'; }
      return o;
    };
    mask.addEventListener('input', () => {
      mask.value = mcpf(mask.value);
      hid.value  = only(mask.value).slice(0,11);
    });
    mask.value = mcpf(mask.value);
    hid.value  = only(mask.value).slice(0,11);
  })();

  (function(){
    const allToggle = document.getElementById('perm_all_toggle');
    const groupToggles = document.querySelectorAll('.perm-group-toggle');
    const checks = () => document.querySelectorAll('.perm-chk');

    if (allToggle) {
      allToggle.addEventListener('change', () => {
        checks().forEach(c => c.checked = allToggle.checked);
      });
    }

    groupToggles.forEach(gt => {
      gt.addEventListener('change', () => {
        const g = gt.getAttribute('data-group');
        document.querySelectorAll('.perm-chk[data-group="'+g+'"]')
          .forEach(c => c.checked = gt.checked);
      });
    });
  })();
</script>
@endsection

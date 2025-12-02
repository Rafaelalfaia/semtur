@extends('console.admin-layout')
@section('title','Novo Usuário')
@section('page.title','Novo Usuário')

@section('content')
@php
  use Illuminate\Support\Str;
@endphp
<div class="mx-auto w-full max-w-[1100px] px-4 md:px-6 py-6 md:py-10 space-y-6">

  {{-- Erros/feedback --}}
  @if ($errors->any())
    <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-200 text-sm">
      <div class="font-semibold mb-1">Corrija os campos abaixo:</div>
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  @if(session('ok'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-200 text-sm">
      {{ session('ok') }}
    </div>
  @endif

  <form method="POST" action="{{ route('admin.usuarios.store') }}" class="space-y-6">
    @csrf

    {{-- Dados básicos --}}
    <section class="rounded-xl border border-white/10 bg-[#0F1412] p-4 md:p-5 space-y-4">
      <h2 class="text-sm font-semibold tracking-wide text-slate-200">Dados básicos</h2>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-slate-300 mb-1" for="name">Nome</label>
          <input id="name" name="name" type="text" value="{{ old('name') }}"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2"
                 required>
          @error('name')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-sm text-slate-300 mb-1" for="email">E-mail (opcional)</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2"
                 placeholder="ex: pessoa@dominio.com">
          @error('email')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-slate-300 mb-1" for="cpf_mask">CPF (opcional)</label>
          <input id="cpf_mask" type="text" inputmode="numeric" placeholder="000.000.000-00"
                 value="{{ old('cpf') ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/','$1.$2.$3-$4',preg_replace('/\D+/','',old('cpf'))) : '' }}"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2">
          <input id="cpf" name="cpf" type="hidden" value="{{ old('cpf') }}">
          @error('cpf')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>
      </div>
    </section>

    {{-- Acesso e segurança --}}
    <section class="rounded-xl border border-white/10 bg-[#0F1412] p-4 md:p-5 space-y-4">
      <h2 class="text-sm font-semibold tracking-wide text-slate-200">Acesso e segurança</h2>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-slate-300 mb-1" for="password">Senha</label>
          <input id="password" name="password" type="password"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2" required>
          @error('password')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm text-slate-300 mb-1" for="password_confirmation">Confirmar senha</label>
          <input id="password_confirmation" name="password_confirmation" type="password"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2" required>
        </div>
      </div>
    </section>

    {{-- Papéis --}}
<section class="rounded-xl border border-white/10 bg-[#0F1412] p-4 md:p-5 space-y-3">
  <div class="flex items-center justify-between">
    <h2 class="text-sm font-semibold tracking-wide text-slate-200">Papéis</h2>
    <span class="text-xs text-slate-400">Selecione um ou mais</span>
  </div>

  @php
    // normaliza seleção anterior (pode ter vindo como nome OU id)
    $oldRoles = collect(old('roles', []))->map(fn($v)=>(string)$v)->all();

    // normaliza lista de papéis: sempre {id, name}
    $roleItems = collect($roles)->map(function($r){
      if (is_object($r)) return ['id'=>(string)$r->id, 'name'=>(string)$r->name];
      $name = (string)$r; // caso raro (veio pluck)
      return ['id'=>$name, 'name'=>$name];
    })->all();
  @endphp

  <select name="roles[]" multiple
          class="w-full min-h-[120px] rounded-lg bg-white/5 border border-white/10 px-3 py-2">
    @foreach($roleItems as $r)
      @php $selected = in_array($r['id'],$oldRoles,true) || in_array($r['name'],$oldRoles,true); @endphp
      <option value="{{ $r['id'] }}" @selected($selected)>{{ $r['name'] }}</option>
    @endforeach
  </select>
  @error('roles')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
</section>



    {{-- Permissões (legível em PT-BR) --}}
<section class="rounded-xl border border-white/10 bg-[#0F1412] p-4 md:p-5 space-y-4">
  @php
    // Nome amigável dos grupos
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

    // Tradução de ações
    $actionLabel = [
      'view'      => 'Visualizar',
      'create'    => 'Criar',
      'update'    => 'Editar',
      'delete'    => 'Excluir',
      'publicar'  => 'Publicar',
      'arquivar'  => 'Arquivar',
      'rascunho'  => 'Marcar como rascunho',
      'manage'    => 'Gerenciar',
      'reordenar' => 'Reordenar',
      'toggle'    => 'Ativar/Desativar',
      'clear'     => 'Limpar',
    ];

    // Nome amigável de sub-recursos (para eventos.*.*)
    $resourceLabel = [
      'atrativos' => 'Atrativos',
      'edicoes'   => 'Edições',
      'midias'    => 'Mídias',
      'cache'     => 'Cache',
    ];

    // Constrói label final da permissão (ex: "eventos.midias.reordenar" -> "Reordenar Mídias")
    $prettyPerm = function(string $full) use ($actionLabel, $resourceLabel){
      if ($full === 'console.cache.clear') return 'Limpar cache do console';
      $parts = explode('.', $full);          // [grupo, (subrecurso?), acao]
      array_shift($parts);                    // remove grupo
      if (count($parts) === 1) {              // [acao]
        $a = $parts[0];
        return $actionLabel[$a] ?? \Illuminate\Support\Str::headline($a);
      }
      // [subrecurso, acao]
      [$sub, $a] = $parts;
      $aLabel = $actionLabel[$a] ?? \Illuminate\Support\Str::headline($a);
      $subLabel = $resourceLabel[$sub] ?? \Illuminate\Support\Str::headline($sub);
      return "{$aLabel} {$subLabel}";
    };

    $oldPerms = collect(old('perms', []))->all();
  @endphp

  <div class="flex items-center justify-between">
    <h2 class="text-sm font-semibold tracking-wide text-slate-200">Permissões (opcional)</h2>
    <label class="inline-flex items-center gap-2 text-xs">
      <input type="checkbox" id="perm_all_toggle" class="rounded border-white/20 bg-white/5">
      Selecionar todas
    </label>
  </div>

  <div class="grid md:grid-cols-2 gap-4">
    @foreach($permissions as $group => $perms)
      <fieldset class="rounded-lg border border-white/5 bg-white/0 p-3">
        <legend class="text-xs font-semibold mb-2 flex items-center justify-between">
          <span>{{ $groupLabel($group) }}</span>
          <label class="inline-flex items-center gap-1 text-[11px] text-slate-300">
            <input type="checkbox" class="perm-group-toggle rounded border-white/20 bg-white/5" data-group="{{ $group }}">
            marcar grupo
          </label>
        </legend>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          @foreach($perms as $perm)
            @php $pid = 'perm_'.str_replace(['.',' '],['_','_'],$perm->name); @endphp
            <label for="{{ $pid }}" class="inline-flex items-center gap-2">
              <input id="{{ $pid }}" type="checkbox" name="perms[]"
                     value="{{ $perm->name }}"
                     class="perm-chk rounded border-white/20 bg-white/5"
                     data-group="{{ $group }}"
                     @checked(in_array($perm->name,$oldPerms))>
              <span class="text-sm">{{ $prettyPerm($perm->name) }}</span>
            </label>
          @endforeach
        </div>
      </fieldset>
    @endforeach
  </div>

  @error('perms')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
</section>

{{-- JS mantém igual (Selecionar todas / por grupo) --}}


    <div class="flex items-center justify-end gap-3">
      <a href="{{ route('admin.usuarios.index') }}"
         class="rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 px-4 py-2">Cancelar</a>
      <button class="rounded-lg bg-emerald-600 hover:bg-emerald-500 px-5 py-2 font-semibold">Salvar</button>
    </div>
  </form>
</div>

{{-- Scripts: máscara CPF + toggles de permissões --}}
<script>
  // máscara visual de CPF; envia hidden só com dígitos
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

  // Selecionar todas / por grupo
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

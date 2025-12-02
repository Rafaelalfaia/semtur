@php
  use Illuminate\Support\Str;

  // Detecta o contexto pelo prefixo da rota OU pelo papel do usuário
  $routeName = optional(request()->route())->getName();
  $prefix    = $routeName ? Str::before($routeName, '.') : null;

  $user = auth()->user();
  $roleCtx =
    in_array($prefix, ['admin','coordenador','tecnico'], true) ? $prefix :
    ($user->hasRole('Admin') ? 'admin' : ($user->hasRole('Coordenador') ? 'coordenador' : 'tecnico'));

  // Layout por papel (Admin usa o layout próprio)
  $layout = $roleCtx === 'admin' ? 'console.admin-layout' : 'console.layout';

  // Para envio: técnico reutiliza as rotas do coordenador
  $submitRole = $roleCtx === 'tecnico' ? 'coordenador' : $roleCtx;

  $updateName      = $submitRole . '.config.perfil.update';
  $deletePhotoName = $submitRole . '.config.perfil.foto.destroy';

  $fallbackAvatar  = asset('imagens/avatar.png');
  $u               = $user;
  $currentAvatar   = $u->avatar_url ?: $fallbackAvatar;

  // CPF formatado visualmente
  $cpfDigits = preg_replace('/\D+/', '', (string)($u->cpf ?? ''));
  $cpfMasked = strlen($cpfDigits)===11
      ? substr($cpfDigits,0,3).'.'.substr($cpfDigits,3,3).'.'.substr($cpfDigits,6,3).'-'.substr($cpfDigits,9,2)
      : '';
@endphp

@extends($layout)

@section('title','Configurações · Perfil')
@section('page.title','Configurações · Perfil')

@section('content')
  @if (session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-700/40 bg-emerald-900/30 px-3 py-2 text-emerald-200 text-sm">
      {{ session('ok') }}
    </div>
  @endif

  <div class="max-w-2xl space-y-8">

    {{-- Avatar --}}
    <div class="rounded-xl border border-white/5 bg-[#0F1412] p-4">
      <h2 class="text-sm font-semibold mb-3">Foto do perfil</h2>
      <div class="flex items-center gap-4">
        <div class="h-20 w-20 rounded-full overflow-hidden ring-2 ring-white/10">
          <img id="avatar-preview" src="{{ $currentAvatar }}" class="h-full w-full object-cover" alt="Avatar">
        </div>
        <div class="space-x-2">
          <label for="avatar" class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm hover:bg-white/10 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" d="M4 7h3l2-2h6l2 2h3v12H4zM12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
            Trocar foto
          </label>
          <input id="avatar" name="avatar" type="file" accept="image/*" class="hidden" form="perfil-form">

          <form id="foto-remove" method="POST" action="{{ route($deletePhotoName) }}" class="inline">
            @csrf @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/0 px-3 py-2 text-sm text-slate-300 hover:bg-white/5">
              Remover
            </button>
          </form>
        </div>
      </div>
      @error('avatar')<p class="text-xs text-rose-300 mt-2">{{ $message }}</p>@enderror
    </div>

    {{-- Dados principais --}}
    <form id="perfil-form" method="POST" action="{{ route($updateName) }}" enctype="multipart/form-data"
          class="rounded-xl border border-white/5 bg-[#0F1412] p-4 space-y-4">
      @csrf @method('PUT')

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-slate-300 mb-1">Nome</label>
          <input type="text" name="name" value="{{ old('name',$u->name) }}"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-600">
          @error('name')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm text-slate-300 mb-1">E-mail</label>
          <input type="email" name="email" value="{{ old('email',$u->email) }}"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-600">
          @error('email')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-slate-300 mb-1">CPF</label>
          <input id="cpf_mask" type="text" inputmode="numeric" placeholder="000.000.000-00"
                 value="{{ old('cpf') ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/','$1.$2.$3-$4',preg_replace('/\D+/','',old('cpf'))) : $cpfMasked }}"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-600">
          <input id="cpf" name="cpf" type="hidden" value="{{ old('cpf',$cpfDigits) }}">
          @error('cpf')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-slate-300 mb-1">Nova senha (opcional)</label>
          <input id="password" name="password" type="password"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-600">
          @error('password')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm text-slate-300 mb-1">Confirmar nova senha</label>
          <input id="password_confirmation" name="password_confirmation" type="password"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-600">
        </div>
      </div>

      <div class="pt-2">
        <button class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 px-4 py-2 font-semibold">
          Salvar alterações
        </button>
      </div>
    </form>
  </div>

  {{-- scripts: preview avatar + máscara CPF --}}
  <script>
    (function(){
      const input = document.getElementById('avatar');
      const img   = document.getElementById('avatar-preview');
      if (!input || !img) return;
      input.addEventListener('change', () => {
        const f = input.files?.[0]; if (!f) return;
        img.src = URL.createObjectURL(f);
      });
    })();

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
    })();
  </script>
@endsection

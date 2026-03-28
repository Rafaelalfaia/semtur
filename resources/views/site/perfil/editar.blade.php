@extends('site.layouts.app')
@section('title','Editar perfil')
@section('meta.description','Área da conta do VisitAltamira para atualizar foto, nome e informações do perfil com segurança.')
@section('meta.image', theme_asset('hero_image'))
@section('meta.canonical', route('site.perfil.editar'))
@section('meta.noindex', 'true')

@section('site.content')
<div class="min-h-dvh bg-white text-slate-900">
  {{-- Top bar --}}
  <header class="sticky top-0 z-10 bg-white/80 backdrop-blur border-b border-slate-100">
    <div class="mx-auto max-w-lg px-4 py-3 flex items-center gap-3">
      <a href="{{ route('site.perfil.index') }}" class="h-10 w-10 rounded-full bg-slate-100 grid place-items-center hover:bg-slate-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <h1 class="text-lg font-semibold">Editar Perfil</h1>
    </div>
  </header>

  <main class="mx-auto max-w-lg px-6 py-6 pb-24">
    @if (session('status'))
      <div class="mb-4 text-xs rounded-lg p-2 bg-emerald-50 border border-emerald-200 text-emerald-700">
        {{ session('status') }}
      </div>
    @endif

    <form method="POST" action="{{ route('site.perfil.atualizar') }}" enctype="multipart/form-data" class="space-y-5" id="perfil-form">
      @csrf @method('PUT')

      {{-- Avatar + botão de upload --}}
      @php
        $fallback = asset('imagens/avatar.png'); // public/imagens/avatar.png
        $foto = $u->avatar_url ? $u->avatar_url : $fallback;
      @endphp

      <div class="flex flex-col items-center">
        <div class="relative h-28 w-28 rounded-full overflow-hidden ring-2 ring-slate-100 bg-slate-200">
          <img id="avatar-preview" src="{{ $foto }}" alt="Seu avatar" class="h-full w-full object-cover">
        </div>

        <label for="avatar" class="mt-3 inline-flex items-center gap-2 rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 cursor-pointer">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" d="M4 7h3l2-2h6l2 2h3v12H4zM12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
          Escolher foto
        </label>
        <input id="avatar" name="avatar" type="file" accept="image/*" class="hidden">
        @error('avatar')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
      </div>

      {{-- Nome --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Nome</label>
        <input name="name" type="text" value="{{ old('name',$u->name) }}" required
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
        @error('name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- E-mail (opcional) --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">E-mail (opcional)</label>
        <input name="email" type="email" value="{{ old('email',$u->email) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
        @error('email')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- CPF: bloqueado se já tiver; senão, pode adicionar (com máscara visual) --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">CPF</label>

        @php
          $cpfDigits = preg_replace('/\D+/','', (string)($u->cpf ?? ''));
          $cpfMasked = strlen($cpfDigits)===11 ? (substr($cpfDigits,0,3).'.'.substr($cpfDigits,3,3).'.'.substr($cpfDigits,6,3).'-'.substr($cpfDigits,9,2)) : '';
        @endphp

        @if($u->cpf)
          {{-- já possui CPF: mostrar bloqueado --}}
          <input type="text" value="{{ $cpfMasked ?: $u->cpf }}" disabled
                 class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50 text-slate-500">
          <p class="text-[11px] text-slate-400 mt-1">CPF vinculado à conta. Para alterar, contate o suporte.</p>
        @else
          {{-- pode cadastrar CPF --}}
          <input id="cpf_mask" type="text" inputmode="numeric" placeholder="000.000.000-00"
                 value="{{ old('cpf') ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', preg_replace('/\D+/','',old('cpf'))) : '' }}"
                 class="mt-1 w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
          <input id="cpf" name="cpf" type="hidden" value="{{ old('cpf') }}">
          @error('cpf')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        @endif
      </div>

      {{-- Telefone (opcional) --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Telefone</label>
        <input name="phone" type="text" value="{{ old('phone',$u->phone) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
        @error('phone')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Alterar senha (opcional, sem senha atual) --}}
<div class="mt-6">
  <h2 class="text-sm font-semibold text-slate-700 mb-2">Alterar senha (opcional)</h2>

  {{-- Nova senha --}}
  <div class="mb-3">
    <label class="block text-sm font-medium text-slate-700">Nova senha</label>
    <div class="mt-1 relative">
      <input id="password" name="password" type="password" autocomplete="new-password"
             class="w-full rounded-xl border-slate-300 pr-12 focus:border-emerald-500 focus:ring-emerald-500">

    </div>
    @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
  </div>

  {{-- Confirmar nova senha --}}
  <div>
    <label class="block text-sm font-medium text-slate-700">Confirmar nova senha</label>
    <div class="mt-1 relative">
      <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
             class="w-full rounded-xl border-slate-300 pr-12 focus:border-emerald-500 focus:ring-emerald-500">

    </div>
  </div>

  <p class="mt-2 text-[11px] text-slate-500">Deixe em branco para manter sua senha atual.</p>
</div>

{{-- Toggle mostrar/ocultar (se ainda não tiver no arquivo) --}}
<script>
  (function () {
    document.querySelectorAll('.pw-toggle').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-for');
        const input = document.getElementById(id);
        if (input) input.type = input.type === 'password' ? 'text' : 'password';
      });
    });
  })();
</script>


      {{-- Salvar --}}
      <div class="pt-2">
        <button class="w-full h-12 rounded-2xl bg-emerald-600 text-white font-semibold hover:bg-emerald-500">
          Salvar
        </button>
      </div>
    </form>
  </main>
</div>

{{-- Scripts: preview do avatar e máscara visual do CPF (envia só dígitos) --}}
<script>
  // Preview do avatar selecionado
  (function () {
    const input = document.getElementById('avatar');
    const img = document.getElementById('avatar-preview');
    if (!input || !img) return;
    input.addEventListener('change', function () {
      const file = this.files && this.files[0];
      if (!file) return;
      const url = URL.createObjectURL(file);
      img.src = url;
    });
  })();

  // Máscara visual de CPF: mostra 000.000.000-00 e envia hidden só com dígitos
  (function () {
    const mask = document.getElementById('cpf_mask');
    const hidden = document.getElementById('cpf');
    if (!mask || !hidden) return;

    function onlyDigits(s){ return (s||'').replace(/\D+/g,''); }
    function maskCpf(d){
      const a = onlyDigits(d).slice(0,11);
      let out=''; for (let i=0;i<a.length;i++){ out+=a[i]; if(i===2||i===5) out+='.'; if(i===8) out+='-'; }
      return out;
    }

    mask.addEventListener('input', () => {
      mask.value = maskCpf(mask.value);
      mask.setSelectionRange(mask.value.length, mask.value.length);
    });

    document.getElementById('perfil-form').addEventListener('submit', () => {
      hidden.value = onlyDigits(mask.value).slice(0,11);
    });

    // rehidrata old()
    if (mask.value) mask.value = maskCpf(mask.value);
  })();
</script>

<script>
  // Toggle mostrar/ocultar senha
  (function () {
    document.querySelectorAll('.pw-toggle').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-for');
        const input = document.getElementById(id);
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
      });
    });
  })();
</script>

{{-- Espaço p/ não cobrir conteúdo (mobile) + bottom nav --}}
<div class="h-[80px] pb-[env(safe-area-inset-bottom)] md:hidden"></div>
@endsection

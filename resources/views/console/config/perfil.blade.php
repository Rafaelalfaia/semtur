@php
  use Illuminate\Support\Str;

  $routeName = optional(request()->route())->getName();
  $prefix = $routeName ? Str::before($routeName, '.') : null;

  $user = auth()->user();
  $roleCtx =
    in_array($prefix, ['admin', 'coordenador', 'tecnico'], true) ? $prefix :
    ($user->hasRole('Admin') ? 'admin' : ($user->hasRole('Coordenador') ? 'coordenador' : 'tecnico'));

  $submitRole = $roleCtx === 'tecnico' ? 'coordenador' : $roleCtx;

  $updateName = $submitRole . '.config.perfil.update';
  $deletePhotoName = $submitRole . '.config.perfil.foto.destroy';

  $fallbackAvatar = asset('imagens/avatar.png');
  $u = $user;
  $currentAvatar = $u->avatar_url ?: $fallbackAvatar;

  $cpfDigits = preg_replace('/\D+/', '', (string) ($u->cpf ?? ''));
  $cpfMasked = strlen($cpfDigits) === 11
      ? substr($cpfDigits, 0, 3) . '.' . substr($cpfDigits, 3, 3) . '.' . substr($cpfDigits, 6, 3) . '-' . substr($cpfDigits, 9, 2)
      : '';
@endphp

@extends('console.layout')

@section('title', 'Configuracoes - Perfil')
@section('page.title', 'Configuracoes - Perfil')
@section('topbar.description', 'Atualize os dados essenciais da conta sem recriar viewport, sidebar ou estrutura do shell.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Perfil</span>
@endsection

@section('content')
  <div class="ui-console-page ui-profile-page">
    <x-dashboard.page-header
      title="Meu perfil"
      subtitle="Visao compacta para ajustar foto, dados principais e credenciais com a mesma linguagem visual do console."
    />

    <div class="ui-profile-layout mt-5">
      <x-dashboard.section-card
        title="Identidade da conta"
        subtitle="Foto institucional e referencia visual da sua conta"
        class="ui-profile-avatar-card"
      >
        <div class="ui-profile-avatar-shell">
          <div class="ui-profile-avatar-media">
            <img id="avatar-preview" src="{{ $currentAvatar }}" class="h-full w-full object-cover" alt="Avatar">
          </div>

          <div class="min-w-0">
            <p class="ui-profile-eyebrow">Imagem de perfil</p>
            <h3 class="ui-profile-name">{{ $u->name }}</h3>
            <p class="ui-profile-copy">
              Essa foto aparece como referencia visual da sua conta nas areas administrativas do console.
            </p>

            <div class="ui-profile-avatar-actions">
              <label for="avatar" class="ui-btn-primary cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-width="1.8" d="M4 7h3l2-2h6l2 2h3v12H4zM12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/>
                </svg>
                Trocar foto
              </label>

              <input id="avatar" name="avatar" type="file" accept="image/*" class="hidden" form="perfil-form">

              <form id="foto-remove" method="POST" action="{{ route($deletePhotoName) }}" class="inline-flex">
                @csrf
                @method('DELETE')
                <button type="submit" class="ui-btn-secondary">Remover</button>
              </form>
            </div>

            @error('avatar')<p class="mt-2 text-xs text-rose-500">{{ $message }}</p>@enderror
          </div>
        </div>
      </x-dashboard.section-card>

      <x-dashboard.section-card
        title="Dados principais"
        subtitle="Dados pessoais e seguranca da conta"
        class="ui-profile-form-card"
      >
        <form id="perfil-form" method="POST" action="{{ route($updateName) }}" enctype="multipart/form-data" class="space-y-5">
          @csrf
          @method('PUT')

          <div class="ui-profile-field-grid">
            <div>
              <label class="ui-profile-label" for="name">Nome</label>
              <input id="name" type="text" name="name" value="{{ old('name', $u->name) }}" class="ui-profile-input">
              @error('name')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div>
              <label class="ui-profile-label" for="email">E-mail</label>
              <input id="email" type="email" name="email" value="{{ old('email', $u->email) }}" class="ui-profile-input">
              @error('email')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>
          </div>

          <div class="ui-profile-field-grid">
            <div>
              <label class="ui-profile-label" for="cpf_mask">CPF</label>
              <input
                id="cpf_mask"
                type="text"
                inputmode="numeric"
                placeholder="000.000.000-00"
                value="{{ old('cpf') ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/','$1.$2.$3-$4',preg_replace('/\D+/','',old('cpf'))) : $cpfMasked }}"
                class="ui-profile-input"
              >
              <input id="cpf" name="cpf" type="hidden" value="{{ old('cpf', $cpfDigits) }}">
              @error('cpf')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>
          </div>

          <div class="ui-profile-divider"></div>

          <div class="ui-profile-field-grid">
            <div>
              <label class="ui-profile-label" for="password">Nova senha</label>
              <input id="password" name="password" type="password" class="ui-profile-input" autocomplete="new-password">
              <p class="ui-profile-help">Preencha apenas se quiser redefinir o acesso.</p>
              @error('password')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div>
              <label class="ui-profile-label" for="password_confirmation">Confirmar nova senha</label>
              <input id="password_confirmation" name="password_confirmation" type="password" class="ui-profile-input" autocomplete="new-password">
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-end gap-3 pt-1">
            <button class="ui-btn-primary">
              Salvar alteracoes
            </button>
          </div>
        </form>
      </x-dashboard.section-card>
    </div>
  </div>

  <script>
    (function () {
      const input = document.getElementById('avatar');
      const img = document.getElementById('avatar-preview');
      if (!input || !img) return;
      input.addEventListener('change', () => {
        const f = input.files?.[0];
        if (!f) return;
        img.src = URL.createObjectURL(f);
      });
    })();

    (function () {
      const mask = document.getElementById('cpf_mask');
      const hid = document.getElementById('cpf');
      if (!mask || !hid) return;
      const only = s => (s || '').replace(/\D+/g, '');
      const mcpf = d => {
        const a = only(d).slice(0, 11);
        let o = '';
        for (let i = 0; i < a.length; i++) {
          o += a[i];
          if (i === 2 || i === 5) o += '.';
          if (i === 8) o += '-';
        }
        return o;
      };
      mask.addEventListener('input', () => {
        mask.value = mcpf(mask.value);
        hid.value = only(mask.value).slice(0, 11);
      });
      mask.value = mcpf(mask.value);
      hid.value = only(mask.value).slice(0, 11);
    })();
  </script>
@endsection

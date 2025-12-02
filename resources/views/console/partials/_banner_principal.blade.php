@php
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Str;

  /** Usuário logado */
  $u = auth()->user();

  /** papel pelo prefixo do nome da rota; fallback pelo papel do usuário */
  $currentName = optional(request()->route())->getName();
  $role = $currentName ? Str::before($currentName, '.') : null;
  if (!$role) {
    $role = $u?->hasRole('Admin')        ? 'admin'
          : ($u?->hasRole('Coordenador') ? 'coordenador'
          : ($u?->hasRole('Tecnico')     ? 'tecnico' : 'coordenador'));
  }

  /** helper: retorna a 1ª rota existente como URL, ou null */
  $resolve = function(array $names): ?string {
    foreach ($names as $n) {
      if ($n && Route::has($n)) return route($n);
    }
    return null;
  };

  /** permissões */
  $canViewDest   = $u && $u->canany(['banners_destaque.view','banners_destaque.manage']);
  $canViewCommon = $u && $u->canany(['banners.view','banners.manage']);
  $canManageAny  = $u && $u->canany(['banners_destaque.manage','banners.manage']);

  /** candidatos para Banner Principal priorizando o papel atual */
  $destByRole = [
    "{$role}.banners-destaque.index",
    "{$role}.banner-destaque.index",
    "{$role}.banners_destaque.index",
    "{$role}.banner_destaque.index",
  ];
  /** fallback: tentar coordenador/admin se acima não existir */
  $destFallback = [
    'coordenador.banners-destaque.index',
    'coordenador.banner-destaque.index',
    'coordenador.banners_destaque.index',
    'coordenador.banner_destaque.index',
    'admin.banners-destaque.index',
    'admin.banner-destaque.index',
    'admin.banners_destaque.index',
    'admin.banner_destaque.index',
  ];

  /** candidatos para Banners comuns */
  $commonByRole = [
    "{$role}.banners.index",
    "{$role}.banner.index",
  ];
  $commonFallback = [
    'coordenador.banners.index',
    'admin.banners.index',
  ];

  /** decide href conforme permissões/rotas disponíveis */
  $banDestHref = null;
  if ($canViewDest) {
    $banDestHref = $resolve(array_merge($destByRole, $destFallback));
  }
  if (!$banDestHref && $canViewCommon) {
    $banDestHref = $resolve(array_merge($commonByRole, $commonFallback));
  }

  /** rótulo do botão conforme permissão de manage */
  $btnLabel = $canManageAny ? 'Configurar' : 'Abrir';
@endphp

@if($banDestHref)
  <div class="mb-6 md:mb-8 rounded-2xl border border-white/10 bg-gradient-to-br from-emerald-900/30 to-emerald-700/10 p-5 md:p-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <div class="text-sm text-emerald-300/90">Banner Principal</div>
        <h3 class="text-xl md:text-2xl font-semibold mt-1">Gerencie os destaques da Home</h3>
        <p class="text-slate-400 mt-1">Use esta área para promover campanhas e conteúdos prioritários.</p>
      </div>

      <a href="{{ $banDestHref }}"
         class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 px-3 py-2 text-sm">
        {{ $btnLabel }}
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M13.172 12L7.05 5.879 8.464 4.464 16 12l-7.536 7.536-1.414-1.415L13.172 12z"/>
        </svg>
      </a>
    </div>
  </div>
@endif

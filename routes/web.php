<?php

use Illuminate\Support\Facades\Route;

// =========================
// CONTROLLERS COMUNS
// =========================
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Console\ProfileController as ConsoleProfile;
use App\Http\Controllers\Auth\GoogleAuthController;

// =========================
// SITE (web) – PÚBLICO
// =========================
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\MapaController;
use App\Http\Controllers\Site\CategoriaController as SiteCategoriaController;
use App\Http\Controllers\Site\PontoController as SitePontoController;
use App\Http\Controllers\Site\EmpresaController as SiteEmpresaController;
use App\Http\Controllers\Site\PerfilController;
use App\Http\Controllers\Site\BannerDestaqueFeedController;
use App\Http\Controllers\Site\SecretariaController as SiteSecretariaController;
use App\Http\Controllers\Site\AvisoFeedController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Site\EventoPublicController;



// =========================
// ADMIN
// =========================
use App\Http\Controllers\Admin\DashboardController as AdminDash;
use App\Http\Controllers\Admin\UsuarioController as AdminUsuario;

// =========================
// COORDENADOR
// =========================
use App\Http\Controllers\Coordenador\DashboardController as CoordDash;
use App\Http\Controllers\Coordenador\CategoriaController;
use App\Http\Controllers\Coordenador\EmpresaController as CoordEmpresaController;
use App\Http\Controllers\Coordenador\PontoTuristicoController;
use App\Http\Controllers\Coordenador\BannerController;
use App\Http\Controllers\Coordenador\BannerDestaqueController;
use App\Http\Controllers\Coordenador\SecretariaController;
use App\Http\Controllers\Coordenador\EquipeMembroController;
use App\Http\Controllers\Coordenador\AvisoController;
use App\Http\Controllers\Coordenador\EventoController;
use App\Http\Controllers\System\MaintenanceController;
use App\Http\Controllers\Coordenador\RelatorioController;
use App\Http\Controllers\Coordenador\TecnicoController;
use App\Http\Controllers\Coordenador\EspacoCulturalController;

// =========================
/* SITE – PÚBLICO (WEB) */
// =========================
Route::get('/',         [HomeController::class, 'index'])->name('site.home');
Route::get('/explorar', [HomeController::class, 'explorar'])->name('site.explorar');
Route::get('/mapa',     [MapaController::class, 'index'])->name('site.mapa');
Route::view('/orgaos',  'site.orgaos')->name('site.orgaos');

Route::get('/banner-destaque-feed', [BannerDestaqueFeedController::class,'index'])
    ->name('site.banner_destaque.feed');

// Detalhes (slug OU id) — **sem duplicações**
Route::get('/ponto/{ponto}',     [SitePontoController::class,   'show'])->name('site.ponto');     // {ponto} = slug|id
Route::get('/empresa/{empresa}', [SiteEmpresaController::class, 'show'])->name('site.empresa');   // {empresa} = slug|id

// Página de categoria
Route::get('/categoria/{slug}', [SiteCategoriaController::class, 'show'])->name('site.categoria');

// Página offline (PWA)
Route::view('/offline', 'offline')->name('offline');

// Política/Privacidade
Route::view('/politica-privacidade', 'site.politicas')->name('site.politicas');



Route::get('/semtur', [SiteSecretariaController::class, 'show'])
    ->name('site.semtur');


// Nova URL canônica (sem "semtur")
Route::get('/secretaria', [SiteSecretariaController::class, 'show'])
    ->name('site.secretaria');




Route::get('/aviso/ativo', [AvisoFeedController::class, 'ativo'])->name('site.aviso.ativo');

Route::get('/auth/google/redirect', fn() => Socialite::driver('google')->redirect())
    ->name('google.redirect');

Route::get('/auth/google/callback', function () {
    $g = Socialite::driver('google')->stateless()->user();

    $googleId = (string) $g->getId();
    $email    = $g->getEmail() ? strtolower($g->getEmail()) : null;
    $name     = $g->getName() ?: $g->getNickname() ?: ($email ? strtok($email, '@') : 'Usuário');

    // 1) tenta por google_id; 2) se não achar e tiver email, tenta por email
    $user = User::where('google_id', $googleId)->first();
    if (!$user && $email) {
        $user = User::where('email', $email)->first();
    }

    if (!$user) {
        // CRIAR novo usuário — precisa senha (NOT NULL)
        $user = User::create([
            'name'       => $name,
            'email'      => $email,            // pode ser null se o Google não retornar (raro)
            'google_id'  => $googleId,
            'avatar_url' => $g->getAvatar(),
            'password'   => Hash::make(Str::random(40)), // senha aleatória apenas para satisfazer NOT NULL
        ]);
    } else {
        // ATUALIZAR existente — NUNCA tocar na senha aqui
        $user->update([
            'name'       => $name,
            'google_id'  => $googleId,         // amarra a conta ao Google para próximos logins
            'avatar_url' => $g->getAvatar(),
            // 'email' => $email ?? $user->email, // evite sobrescrever se o Google não trouxer
        ]);
    }

    // Papel padrão: Cidadao apenas se ainda não tiver nenhum role
    if (method_exists($user, 'roles') && method_exists($user, 'assignRole')) {
        if (!$user->roles()->exists()) {
            $user->assignRole('Cidadao');
        }
    }

    Auth::login($user, remember: true);
    return redirect()->intended('/');
})->name('google.callback');

Route::get('/ig-img', function (\Illuminate\Http\Request $r) {
    $u = $r->query('u');
    if (!$u || !Str::startsWith($u, ['http://','https://'])) {
        abort(400,'bad url');
    }

    try {
        // pega imagem sem enviar referer
        $resp = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0',
                'Accept'     => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
            ])
            ->get($u);

        if (!$resp->ok()) {
            abort($resp->status(), 'upstream error');
        }

        $ctype = $resp->header('Content-Type','image/jpeg');
        // cache público por 6h
        return response($resp->body(), 200)
            ->header('Content-Type', $ctype)
            ->header('Cache-Control', 'public, max-age=21600');
    } catch (\Throwable $e) {
        abort(502,'proxy fail');
    }
})->name('proxy.ig');

Route::get('/eventos', [EventoPublicController::class,'index'])->name('eventos.index');
Route::get('/eventos/{slug}/{ano?}', [EventoPublicController::class,'show'])->name('eventos.show');

// =========================
// PERFIL/CONTA (CIDADAO)
// =========================
Route::middleware(['auth','role:Cidadao'])
    ->prefix('conta')->as('site.perfil.')
    ->group(function () {
        Route::get('/',        [PerfilController::class,'index'])->name('index');
        Route::get('/editar',  [PerfilController::class,'editar'])->name('editar');
        Route::put('/editar',  [PerfilController::class,'atualizar'])->name('atualizar');

        Route::get('/redes',   [PerfilController::class,'redes'])->name('redes');
        Route::put('/redes',   [PerfilController::class,'redesAtualizar'])->name('redes.atualizar');
    });


// =========================
// DASHBOARD / AUTENTICADOS
// =========================
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $u = auth()->user();

        if ($u->hasRole('Admin'))       return redirect()->route('admin.dashboard');
        if ($u->hasRole('Coordenador')) return redirect()->route('coordenador.dashboard');

        // Técnico: manda para o 1º módulo permitido
        if ($u->hasRole('Tecnico')) {
            $preferencias = [
                ['perm' => 'pontos.view',           'route' => 'coordenador.pontos.index'],
                ['perm' => 'empresas.view',         'route' => 'coordenador.empresas.index'],
                ['perm' => 'categorias.view',       'route' => 'coordenador.categorias.index'],
                ['perm' => 'eventos.view',          'route' => 'coordenador.eventos.index'],
                ['perm' => 'banners_destaque.view', 'route' => 'coordenador.banners-destaque.index'],
                ['perm' => 'banners.view',          'route' => 'coordenador.banners.index'],
                ['perm' => 'avisos.view',           'route' => 'coordenador.avisos.index'],
                ['perm' => 'relatorios.view',       'route' => 'coord.relatorios.index'],
                ['perm' => 'secretaria.edit',       'route' => 'coordenador.secretaria.edit'],
            ];
            foreach ($preferencias as $p) {
                if ($u->can($p['perm']) && Route::has($p['route'])) {
                    return redirect()->route($p['route']);
                }
            }
            return redirect()->route('coordenador.dashboard');
        }

        if ($u->hasRole('Cidadao')) return redirect()->route('site.perfil.index');

        abort(403, 'Sem painel associado ao seu papel.');
    })->name('dashboard');
});


// Perfil (Laravel Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    // Logout
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// ADMIN
Route::middleware(['auth','role:Admin'])
  ->prefix('admin')->name('admin.')
  ->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

    Route::prefix('config')->name('config.')->group(function () {
      Route::get('perfil',        [ConsoleProfile::class,'edit'])->name('perfil.edit');
      Route::put('perfil',        [ConsoleProfile::class,'update'])->name('perfil.update');
      Route::delete('perfil/foto',[ConsoleProfile::class,'destroyPhoto'])->name('perfil.foto.destroy');
    });

    Route::resource('usuarios', \App\Http\Controllers\Admin\UsuarioController::class)->except(['show']);
  });

// =========================
/* COORDENADOR */
// =========================
Route::middleware(['auth','role:Coordenador|Tecnico'])
    ->prefix('coordenador')->name('coordenador.')
    ->group(function () {

        Route::get('/dashboard', [CoordDash::class,'index'])->name('dashboard');

        Route::prefix('config')->name('config.')->group(function () {
            Route::get('perfil',       [ConsoleProfile::class,'edit'])->name('perfil.edit');
            Route::put('perfil',       [ConsoleProfile::class,'update'])->name('perfil.update');
            Route::delete('perfil/foto',[ConsoleProfile::class,'destroyPhoto'])->name('perfil.foto.destroy');
        });

        // Categorias
        Route::resource('categorias', CategoriaController::class)->except(['show']);
        Route::patch('categorias/{categoria}/publicar', [CategoriaController::class,'publicar'])->name('categorias.publicar');
        Route::patch('categorias/{categoria}/arquivar', [CategoriaController::class,'arquivar'])->name('categorias.arquivar');
        Route::patch('categorias/{categoria}/rascunho', [CategoriaController::class,'rascunho'])->name('categorias.rascunho');
        Route::delete('categorias/{categoria}/icone',   [CategoriaController::class,'removerIcone'])->name('categorias.icone.remover');

        // Empresas
        Route::resource('empresas', CoordEmpresaController::class)->except(['show']);
        Route::patch('empresas/{empresa}/publicar', [CoordEmpresaController::class,'publicar'])->name('empresas.publicar');
        Route::patch('empresas/{empresa}/arquivar', [CoordEmpresaController::class,'arquivar'])->name('empresas.arquivar');
        Route::patch('empresas/{empresa}/rascunho', [CoordEmpresaController::class,'rascunho'])->name('empresas.rascunho');

        // mídia (novo + aliases legados)
        Route::delete('empresas/{empresa}/capa',   [CoordEmpresaController::class,'removerCapa'])->name('empresas.capa.remover');
        Route::delete('empresas/{empresa}/perfil', [CoordEmpresaController::class,'removerPerfil'])->name('empresas.perfil.remover');
        Route::delete('empresas/{empresa}/remover-capa',   [CoordEmpresaController::class,'removerCapa'])->name('empresas.removerCapa');
        Route::delete('empresas/{empresa}/remover-perfil', [CoordEmpresaController::class,'removerPerfil'])->name('empresas.removerPerfil');

        // recomendações (empresas)
        Route::post('empresas/{empresa}/recomendar',         [CoordEmpresaController::class,'recomendar'])->name('empresas.recomendar');
        Route::delete('empresas/{empresa}/recomendar',       [CoordEmpresaController::class,'removerRecomendacao'])->name('empresas.recomendar.remover');
        Route::patch('empresas/recomendacoes/{rec}/ordem',   [CoordEmpresaController::class,'reordenarRecomendacao'])->name('empresas.recomendar.ordem');
        Route::patch('empresas/recomendacoes/{rec}/ordenar', [CoordEmpresaController::class,'reordenarRecomendacao'])->name('empresas.recomendar.ordenar');

        // Pontos turísticos
        Route::resource('pontos', PontoTuristicoController::class)->except(['show']);
        Route::patch('pontos/{ponto}/publicar', [PontoTuristicoController::class,'publicar'])->name('pontos.publicar');
        Route::patch('pontos/{ponto}/arquivar', [PontoTuristicoController::class,'arquivar'])->name('pontos.arquivar');
        Route::patch('pontos/{ponto}/rascunho', [PontoTuristicoController::class,'rascunho'])->name('pontos.rascunho');

        // capa do ponto (novo + alias legado)
        Route::delete('pontos/{ponto}/capa',           [PontoTuristicoController::class,'removerCapa'])->name('pontos.capa.remover');
        Route::delete('pontos/{ponto}/remover-capa',   [PontoTuristicoController::class,'removerCapa'])->name('pontos.removerCapa');


        // mídias do ponto
        Route::post('pontos/{ponto}/midias/imagens',    [PontoTuristicoController::class,'adicionarImagens'])->name('pontos.midias.imagens.add');
        Route::post('pontos/{ponto}/midias/video-link', [PontoTuristicoController::class,'adicionarVideoLink'])->name('pontos.midias.video.link');
        Route::post('pontos/{ponto}/midias/video-file', [PontoTuristicoController::class,'adicionarVideoFile'])->name('pontos.midias.video.file');
        Route::delete('pontos/midias/{midia}',          [PontoTuristicoController::class,'removerMidia'])->name('pontos.midias.destroy');

        // recomendações (pontos)
        Route::post('pontos/{ponto}/recomendar',          [PontoTuristicoController::class,'recomendar'])->name('pontos.recomendar');
        Route::delete('pontos/{ponto}/recomendar',        [PontoTuristicoController::class,'removerRecomendacao'])->name('pontos.recomendar.remover');
        Route::patch('pontos/recomendacoes/{rec}/ordem',  [PontoTuristicoController::class,'reordenarRecomendacao'])->name('pontos.recomendar.ordem');
        Route::patch('pontos/recomendacoes/{rec}/ordenar',[PontoTuristicoController::class,'reordenarRecomendacao'])->name('pontos.recomendar.ordenar');
   // -- Banners
        Route::resource('banners', BannerController::class)->except(['show']);

  // ---- Banners principais
        Route::resource('banners-destaque', BannerDestaqueController::class)
            ->parameters(['banners-destaque' => 'banner'])
            ->except(['show']);

        Route::put('banners-destaque/{banner}/toggle', [BannerDestaqueController::class,'toggle'])
            ->name('banners-destaque.toggle');

        Route::post('banners-destaque/reordenar', [BannerDestaqueController::class,'reordenar'])
            ->name('banners-destaque.reordenar');

        Route::get('secretaria', [SecretariaController::class,'edit'])
            ->middleware('permission:secretaria.edit')
            ->name('secretaria.edit');

        Route::put('secretaria', [SecretariaController::class,'update'])
            ->middleware('permission:secretaria.edit')
            ->name('secretaria.update');
        // Equipe
        Route::resource('equipe', EquipeMembroController::class)
        ->middleware('permission:equipe.manage')
        ->parameters(['equipe' => 'equipe']);

        Route::resource('avisos', AvisoController::class)->parameters(['avisos' => 'aviso']);
        Route::delete('avisos/{aviso}/imagem', [AvisoController::class, 'removerImagem'])
            ->name('avisos.imagem.remover');
        Route::patch('avisos/{aviso}/publicar', [AvisoController::class, 'publicar'])
            ->name('avisos.publicar');
        Route::patch('avisos/{aviso}/arquivar', [AvisoController::class, 'arquivar'])
            ->name('avisos.arquivar');

            // EVENTOS
        Route::get('eventos',                [EventoController::class,'index'])->name('eventos.index');
        Route::get('eventos/create',         [EventoController::class,'create'])->name('eventos.create');
        Route::post('eventos',               [EventoController::class,'store'])->name('eventos.store');
        Route::get('eventos/{evento}/edit',  [EventoController::class,'edit'])->name('eventos.edit');
        Route::put('eventos/{evento}',       [EventoController::class,'update'])->name('eventos.update');
        Route::delete('eventos/{evento}',    [EventoController::class,'destroy'])->name('eventos.destroy');

        // EDIÇÕES
        Route::get('eventos/{evento}/edicoes',         [EventoController::class,'edicoesIndex'])->name('eventos.edicoes.index');
        Route::get('eventos/{evento}/edicoes/create',  [EventoController::class,'edicoesCreate'])->name('eventos.edicoes.create');
        Route::post('eventos/{evento}/edicoes',        [EventoController::class,'edicoesStore'])->name('eventos.edicoes.store');

        Route::get('edicoes/{edicao}/edit',            [EventoController::class,'edicoesEdit'])->name('edicoes.edit');
        Route::put('edicoes/{edicao}',                 [EventoController::class,'edicoesUpdate'])->name('edicoes.update');
        Route::delete('edicoes/{edicao}',              [EventoController::class,'edicoesDestroy'])->name('edicoes.destroy');

        // ATRATIVOS
        Route::get('edicoes/{edicao}/atrativos',                  [EventoController::class,'atrativosIndex'])->name('edicoes.atrativos.index');
        Route::get('edicoes/{edicao}/atrativos/create',           [EventoController::class,'atrativosCreate'])->name('edicoes.atrativos.create');
        Route::post('edicoes/{edicao}/atrativos',                 [EventoController::class,'atrativosStore'])->name('edicoes.atrativos.store');

        Route::get('atrativos/{atrativo}/edit',                   [EventoController::class,'atrativosEdit'])->name('atrativos.edit');
        Route::put('atrativos/{atrativo}',                        [EventoController::class,'atrativosUpdate'])->name('atrativos.update');
        Route::delete('atrativos/{atrativo}',                     [EventoController::class,'atrativosDestroy'])->name('atrativos.destroy');
        Route::post('edicoes/{edicao}/atrativos/reordenar',       [EventoController::class,'atrativosReordenar'])->name('edicoes.atrativos.reordenar');

        //ESPAÇO CULTURAL
        Route::resource('espacos-culturais', EspacoCulturalController::class)
            ->parameters(['espacos-culturais' => 'espaco'])
            ->except(['show']);

        Route::patch('espacos-culturais/{espaco}/publicar', [EspacoCulturalController::class, 'publicar'])
            ->name('espacos-culturais.publicar');

        Route::patch('espacos-culturais/{espaco}/arquivar', [EspacoCulturalController::class, 'arquivar'])
            ->name('espacos-culturais.arquivar');

        Route::patch('espacos-culturais/{espaco}/rascunho', [EspacoCulturalController::class, 'rascunho'])
            ->name('espacos-culturais.rascunho');


        // GALERIA
        Route::get('edicoes/{edicao}/midias',                     [EventoController::class,'midiasIndex'])->name('edicoes.midias.index');
        Route::post('edicoes/{edicao}/midias',                    [EventoController::class,'midiasStore'])->name('edicoes.midias.store');
        Route::delete('midias/{midia}',                           [EventoController::class,'midiasDestroy'])->name('midias.destroy');
        Route::post('edicoes/{edicao}/midias/reordenar',          [EventoController::class,'midiasReordenar'])->name('edicoes.midias.reordenar');

        Route::get('/secretaria', [SecretariaController::class, 'edit'])
          ->name('secretaria.edit');

        Route::put('/secretaria', [SecretariaController::class, 'update'])
            ->name('secretaria.update');


        // Relatórios
        Route::get('/relatorios', [RelatorioController::class, 'index'])
            ->middleware('permission:relatorios.view')
            ->name('coord.relatorios.index');
        Route::get('/relatorios/exportar.csv', [RelatorioController::class, 'exportCsv'])
            ->middleware('permission:relatorios.view')
            ->name('coord.relatorios.csv');

        Route::middleware('permission:tecnicos.manage')->prefix('tecnicos')->name('tecnicos.')->group(function () {
            Route::get('/',           [TecnicoController::class,'index'])->name('index');
            Route::get('/create',     [TecnicoController::class,'create'])->name('create');
            Route::post('/',          [TecnicoController::class,'store'])->name('store');
            Route::get('/{user}/edit',[TecnicoController::class,'edit'])->name('edit');
            Route::put('/{user}',     [TecnicoController::class,'update'])->name('update');
            Route::delete('/{user}',  [TecnicoController::class,'destroy'])->name('destroy');
            });


    });

        Route::post('/console/cache/clear', [MaintenanceController::class, 'clear'])
        ->middleware(['auth','role:Admin|Coordenador|Tecnico','throttle:3,1'])
        ->name('console.cache.clear');




    // TÉCNICO (compatibilidade de URL)
    Route::middleware(['auth','role:Tecnico'])
    ->prefix('tecnico')->name('tecnico.')
    ->group(function () {
        // /tecnico/dashboard -> para o módulo do coordenador que o técnico pode ver
        Route::get('/dashboard', function () {
            $u = auth()->user();
            if ($u->can('pontos.view') && Route::has('coordenador.pontos.index')) {
                return redirect()->route('coordenador.pontos.index');
            }
            return redirect()->route('dashboard');
        })->name('dashboard');

        // (opcional) manter URL /tecnico/config/perfil funcionando, apontando para a tela única
        Route::get('config/perfil', function () {
            return redirect()->route('coordenador.config.perfil.edit');
        })->name('config.perfil.edit');
    });



require __DIR__.'/auth.php';

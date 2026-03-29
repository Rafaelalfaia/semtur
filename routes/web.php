<?php

use Illuminate\Support\Facades\Route;

// =========================
// CONTROLLERS COMUNS
// =========================
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Console\ProfileController as ConsoleProfile;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;

// =========================
// SITE (web) â€“ PÃšBLICO
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
use App\Http\Controllers\Site\PortalController;
use App\Http\Controllers\Site\EspacoCulturalPublicController;
use App\Http\Controllers\Site\EspacoCulturalAgendamentoPublicController;
use App\Http\Controllers\Site\RoteiroController as SiteRoteiroController;
use App\Http\Controllers\Site\OndeComerController as SiteOndeComerController;
use App\Http\Controllers\Site\OndeFicarController as SiteOndeFicarController;
use App\Http\Controllers\Site\GuiaController as SiteGuiaController;
use App\Http\Controllers\Site\VideoController as SiteVideoController;
use App\Http\Controllers\Site\RotaDoCacauController as SiteRotaDoCacauController;
use App\Http\Controllers\Site\JogosIndigenasPublicController;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Empresa;
use App\Models\Catalogo\EspacoCultural;
use App\Models\Catalogo\PontoTuristico;
use App\Models\Catalogo\Roteiro;
use App\Models\Conteudo\GuiaRevista;
use App\Models\Conteudo\Video;
use App\Models\RotaDoCacau;

$redirectToLocalized = function (string $routeName, array $routeKeys = []) {
    return function (\Illuminate\Http\Request $request, ...$segments) use ($routeName, $routeKeys) {
        $params = ['locale' => route_locale(null, $request)];

        foreach ($routeKeys as $index => $key) {
            $params[$key] = $segments[$index] ?? null;
        }

        return redirect()->route($routeName, array_merge(
            array_filter($params, fn ($value) => $value !== null),
            $request->query()
        ));
    };
};


// =========================
// ADMIN
// =========================
use App\Http\Controllers\Admin\DashboardController as AdminDash;
use App\Http\Controllers\Admin\ThemeController as AdminThemeController;
use App\Http\Controllers\Admin\BackupController as AdminBackupController;
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
use App\Http\Controllers\Coordenador\ThemeExecutionController;
use App\Http\Controllers\Coordenador\EquipeMembroController;
use App\Http\Controllers\Coordenador\AvisoController;
use App\Http\Controllers\Coordenador\EventoController;
use App\Http\Controllers\System\MaintenanceController;
use App\Http\Controllers\Coordenador\RelatorioController;
use App\Http\Controllers\Coordenador\TecnicoController;
use App\Http\Controllers\Coordenador\EspacoCulturalController;
use App\Http\Controllers\Coordenador\EspacoCulturalAgendamentoController;
use App\Http\Controllers\Coordenador\RoteiroController as CoordRoteiroController;
use App\Http\Controllers\Coordenador\OndeComerController as CoordOndeComerController;
use App\Http\Controllers\Coordenador\OndeFicarController as CoordOndeFicarController;
use App\Http\Controllers\Coordenador\GuiaRevistaController as CoordGuiaRevistaController;
use App\Http\Controllers\Coordenador\VideoController as CoordVideoController;
use App\Http\Controllers\Coordenador\JogosIndigenasController as CoordJogosIndigenasController;
use App\Http\Controllers\Coordenador\JogosIndigenasEdicaoController as CoordJogosIndigenasEdicaoController;
use App\Http\Controllers\Coordenador\JogosIndigenasEdicaoFotoController as CoordJogosIndigenasEdicaoFotoController;
use App\Http\Controllers\Coordenador\JogosIndigenasEdicaoVideoController as CoordJogosIndigenasEdicaoVideoController;
use App\Http\Controllers\Coordenador\JogosIndigenasEdicaoPatrocinadorController as CoordJogosIndigenasEdicaoPatrocinadorController;
use App\Http\Controllers\Coordenador\RotaDoCacauController as CoordRotaDoCacauController;
use App\Http\Controllers\Coordenador\RotaDoCacauEdicaoController as CoordRotaDoCacauEdicaoController;
use App\Http\Controllers\Coordenador\RotaDoCacauEdicaoFotoController as CoordRotaDoCacauEdicaoFotoController;
use App\Http\Controllers\Coordenador\RotaDoCacauEdicaoVideoController as CoordRotaDoCacauEdicaoVideoController;
use App\Http\Controllers\Coordenador\RotaDoCacauEdicaoPatrocinadorController as CoordRotaDoCacauEdicaoPatrocinadorController;

// =========================
/* SITE â€“ PÃšBLICO (WEB) */
// =========================
Route::get('/sitemap.xml', function () {
    $locales = array_keys(config('app.supported_locales', ['pt' => []]));
    $staticDefinitions = [
        ['name' => 'site.home', 'changefreq' => 'daily', 'priority' => '1.0'],
        ['name' => 'site.explorar', 'changefreq' => 'daily', 'priority' => '0.95'],
        ['name' => 'site.mapa', 'changefreq' => 'daily', 'priority' => '0.9'],
        ['name' => 'site.agenda', 'changefreq' => 'daily', 'priority' => '0.85'],
        ['name' => 'site.informacoes', 'changefreq' => 'monthly', 'priority' => '0.7'],
        ['name' => 'site.contato', 'changefreq' => 'monthly', 'priority' => '0.6'],
        ['name' => 'site.politicas', 'changefreq' => 'yearly', 'priority' => '0.3'],
        ['name' => 'site.secretaria', 'changefreq' => 'monthly', 'priority' => '0.65'],
        ['name' => 'site.roteiros', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['name' => 'site.onde_comer', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['name' => 'site.onde_ficar', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['name' => 'site.videos', 'changefreq' => 'weekly', 'priority' => '0.75'],
        ['name' => 'site.guias', 'changefreq' => 'weekly', 'priority' => '0.75'],
        ['name' => 'site.museus', 'changefreq' => 'weekly', 'priority' => '0.75'],
        ['name' => 'site.jogos_indigenas.index', 'changefreq' => 'weekly', 'priority' => '0.7'],
        ['name' => 'site.rota_do_cacau.index', 'changefreq' => 'weekly', 'priority' => '0.7'],
    ];

    $staticUrls = collect($locales)->flatMap(function ($locale) use ($staticDefinitions) {
        return collect($staticDefinitions)->map(function ($definition) use ($locale) {
            return [
                'loc' => route($definition['name'], ['locale' => $locale]),
                'changefreq' => $definition['changefreq'],
                'priority' => $definition['priority'],
                'lastmod' => now(),
            ];
        });
    });

    $dynamicUrls = collect();

    $appendUrls = function ($items, string $routeName, string $routeParam = 'slug', string $sourceKey = 'slug', string $changefreq = 'weekly', string $priority = '0.7') use ($dynamicUrls, $locales) {
        $items->each(function ($item) use ($dynamicUrls, $routeName, $routeParam, $sourceKey, $changefreq, $priority, $locales) {
            $slug = data_get($item, $sourceKey);

            if (! filled($slug)) {
                return;
            }

            foreach ($locales as $locale) {
                $dynamicUrls->push([
                    'loc' => route($routeName, ['locale' => $locale, $routeParam => $slug]),
                    'changefreq' => $changefreq,
                    'priority' => $priority,
                    'lastmod' => data_get($item, 'updated_at') ?: data_get($item, 'published_at') ?: now(),
                ]);
            }
        });
    };

    $appendUrls(Categoria::query()->where('status', 'publicado')->select('slug', 'updated_at', 'published_at')->get(), 'site.categoria', 'slug', 'slug', 'weekly', '0.7');
    $appendUrls(PontoTuristico::query()->where('status', 'publicado')->select('slug', 'updated_at', 'published_at')->get(), 'site.ponto', 'ponto', 'slug', 'weekly', '0.8');
    $appendUrls(Empresa::query()->where('status', 'publicado')->select('slug', 'updated_at', 'published_at')->get(), 'site.empresa', 'empresa', 'slug', 'weekly', '0.8');
    $appendUrls(Roteiro::query()->where('status', 'publicado')->select('slug', 'updated_at', 'published_at')->get(), 'site.roteiros.show', 'slug', 'slug', 'weekly', '0.75');
    $appendUrls(EspacoCultural::query()->where('status', 'publicado')->select('slug', 'updated_at', 'published_at')->get(), 'site.museus.show', 'slug', 'slug', 'weekly', '0.75');
    $appendUrls(GuiaRevista::query()->where('status', 'publicado')->select('slug', 'updated_at', 'published_at')->get(), 'site.guias.show', 'slug', 'slug', 'weekly', '0.7');
    $appendUrls(Video::query()->where('status', 'publicado')->select('slug', 'updated_at', 'published_at')->get(), 'site.videos.show', 'slug', 'slug', 'weekly', '0.7');
    $appendUrls(RotaDoCacau::query()->where('status', 'publicado')->select('slug', 'updated_at', 'published_at')->get(), 'site.rota_do_cacau.show', 'slug', 'slug', 'weekly', '0.7');

    $urls = $staticUrls
        ->merge($dynamicUrls)
        ->unique('loc')
        ->values();

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach ($urls as $url) {
        $xml .= '<url>';
        $xml .= '<loc>'.e($url['loc']).'</loc>';
        $xml .= '<lastmod>'.optional($url['lastmod'])->toAtomString().'</lastmod>';
        $xml .= '<changefreq>'.$url['changefreq'].'</changefreq>';
        $xml .= '<priority>'.$url['priority'].'</priority>';
        $xml .= '</url>';
    }

    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
})->name('sitemap');

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])
    ->name('google.redirect');

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
    ->name('google.callback');

Route::get('/', function (\Illuminate\Http\Request $request) {
    return redirect()->route('site.home', ['locale' => route_locale(null, $request)]);
});
Route::get('/explorar', $redirectToLocalized('site.explorar'));
Route::get('/mapa', $redirectToLocalized('site.mapa'));
Route::get('/roteiros', $redirectToLocalized('site.roteiros'));
Route::get('/roteiros/{slug}', $redirectToLocalized('site.roteiros.show', ['slug']));
Route::get('/agenda', $redirectToLocalized('site.agenda'));
Route::get('/onde-comer', $redirectToLocalized('site.onde_comer'));
Route::get('/onde-ficar', $redirectToLocalized('site.onde_ficar'));
Route::get('/videos', $redirectToLocalized('site.videos'));
Route::get('/videos/{slug}', $redirectToLocalized('site.videos.show', ['slug']));
Route::get('/guias', $redirectToLocalized('site.guias'));
Route::get('/guias/{slug}', $redirectToLocalized('site.guias.show', ['slug']));
Route::get('/informacoes-uteis', $redirectToLocalized('site.informacoes'));
Route::get('/contato', $redirectToLocalized('site.contato'));
Route::get('/jogos-indigenas', $redirectToLocalized('site.jogos_indigenas.index'));
Route::get('/rota-do-cacau', $redirectToLocalized('site.rota_do_cacau.index'));
Route::get('/rota-do-cacau/{slug}', $redirectToLocalized('site.rota_do_cacau.show', ['slug']));
Route::get('/museus-e-teatros', $redirectToLocalized('site.museus'));
Route::get('/museus-e-teatros/solicitacoes/{protocolo}', $redirectToLocalized('site.museus.agendamentos.show', ['protocolo']));
Route::get('/museus-e-teatros/solicitacoes/{protocolo}/whatsapp', $redirectToLocalized('site.museus.agendamentos.whatsapp', ['protocolo']));
Route::get('/museus-e-teatros/{espaco}/agendar', $redirectToLocalized('site.museus.agendar', ['espaco']));
Route::get('/museus-e-teatros/{slug}', $redirectToLocalized('site.museus.show', ['slug']));
Route::get('/banner-destaque-feed', $redirectToLocalized('site.banner_destaque.feed'));
Route::get('/ponto/{ponto}', $redirectToLocalized('site.ponto', ['ponto']));
Route::get('/empresa/{empresa}', $redirectToLocalized('site.empresa', ['empresa']));
Route::get('/categoria/{slug}', $redirectToLocalized('site.categoria', ['slug']));
Route::get('/offline', $redirectToLocalized('offline'));
Route::get('/politica-privacidade', $redirectToLocalized('site.politicas'));
Route::get('/semtur', $redirectToLocalized('site.semtur'));
Route::get('/secretaria', $redirectToLocalized('site.secretaria'));
Route::get('/aviso/ativo', $redirectToLocalized('site.aviso.ativo'));
Route::get('/eventos', $redirectToLocalized('eventos.index'));
Route::get('/eventos/{slug}/{ano?}', $redirectToLocalized('eventos.show', ['slug', 'ano']));
Route::get('/conta', $redirectToLocalized('site.perfil.index'));
Route::get('/conta/editar', $redirectToLocalized('site.perfil.editar'));
Route::get('/conta/redes', $redirectToLocalized('site.perfil.redes'));
Route::get('/login', $redirectToLocalized('login'));
Route::get('/register', $redirectToLocalized('register'));
Route::get('/forgot-password', $redirectToLocalized('password.request'));

Route::middleware('app.setLocale')
    ->prefix('{locale}')
    ->where(['locale' => 'pt|en|es'])
    ->group(function () {
Route::get('/',         [HomeController::class, 'index'])->name('site.home');
Route::get('/manifest.webmanifest', function (string $locale) {
    $supported = config('app.supported_locales', []);
    $meta = $supported[$locale] ?? $supported[config('app.locale_prefix_fallback', 'pt')] ?? [];

    $manifest = [
        'name' => 'VisitAltamira',
        'short_name' => 'VisitAltamira',
        'description' => 'Guia oficial de Altamira e do Rio Xingu no Para: pontos turisticos, experiencias, gastronomia e servicos para planejar a visita com mais contexto.',
        'lang' => data_get($meta, 'html_lang', $locale === 'pt' ? 'pt-BR' : $locale),
        'start_url' => '/'.$locale,
        'scope' => '/'.$locale.'/',
        'display' => 'standalone',
        'background_color' => '#0e1b12',
        'theme_color' => '#0e1b12',
        'icons' => [
            [
                'src' => '/icons/pwa-192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
            ],
            [
                'src' => '/icons/pwa-512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
            ],
            [
                'src' => '/icons/pwa-512-maskable.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
            [
                'src' => '/icons/apple-touch-icon.png',
                'sizes' => '180x180',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
        ],
    ];

    return response()->json($manifest)->header('Content-Type', 'application/manifest+json');
})->name('site.manifest');
Route::get('/explorar', [HomeController::class, 'explorar'])->name('site.explorar');
Route::get('/mapa',     [MapaController::class, 'index'])->name('site.mapa');

Route::get('/roteiros', [SiteRoteiroController::class, 'index'])->name('site.roteiros');
Route::get('/roteiros/{slug}', [SiteRoteiroController::class, 'show'])->name('site.roteiros.show');

Route::get('/agenda', [PortalController::class, 'agenda'])->name('site.agenda');

Route::get('/onde-comer', [SiteOndeComerController::class, 'show'])
    ->name('site.onde_comer');

Route::get('/onde-ficar', [SiteOndeFicarController::class, 'show'])
    ->name('site.onde_ficar');

Route::get('/videos', [SiteVideoController::class, 'index'])->name('site.videos');
Route::get('/videos/{slug}', [SiteVideoController::class, 'show'])->name('site.videos.show');


Route::get('/guias', [SiteGuiaController::class, 'index'])->name('site.guias');
Route::get('/guias/{slug}', [SiteGuiaController::class, 'show'])->name('site.guias.show');

Route::get('/informacoes-uteis', [PortalController::class, 'informacoes'])->name('site.informacoes');
Route::get('/contato', [PortalController::class, 'contato'])->name('site.contato');
Route::get('/jogos-indigenas', [JogosIndigenasPublicController::class, 'index'])->name('site.jogos_indigenas.index');
Route::get('/rota-do-cacau', [SiteRotaDoCacauController::class, 'index'])->name('site.rota_do_cacau.index');
Route::get('/rota-do-cacau/{slug}', [SiteRotaDoCacauController::class, 'show'])->name('site.rota_do_cacau.show');

Route::prefix('museus-e-teatros')->group(function () {
    Route::get('/', [EspacoCulturalPublicController::class, 'index'])
        ->name('site.museus');

    Route::get('/solicitacoes/{protocolo}', [EspacoCulturalAgendamentoPublicController::class, 'show'])
        ->name('site.museus.agendamentos.show');

    Route::get('/solicitacoes/{protocolo}/whatsapp', [EspacoCulturalAgendamentoPublicController::class, 'whatsapp'])
        ->name('site.museus.agendamentos.whatsapp');

    Route::get('/{espaco:slug}/agendar', [EspacoCulturalAgendamentoPublicController::class, 'create'])
        ->name('site.museus.agendar');

    Route::post('/{espaco:slug}/agendar', [EspacoCulturalAgendamentoPublicController::class, 'store'])
        ->name('site.museus.agendar.store');

    Route::get('/{slug}', [EspacoCulturalPublicController::class, 'show'])
        ->name('site.museus.show');
});


Route::get('/banner-destaque-feed', [BannerDestaqueFeedController::class,'index'])
    ->name('site.banner_destaque.feed');

// Detalhes (slug OU id) â€” **sem duplicaÃ§Ãµes**
Route::get('/ponto/{ponto}',     [SitePontoController::class,   'show'])->name('site.ponto');     // {ponto} = slug|id
Route::get('/empresa/{empresa}', [SiteEmpresaController::class, 'show'])->name('site.empresa');   // {empresa} = slug|id

// PÃ¡gina de categoria
Route::get('/categoria/{slug}', [SiteCategoriaController::class, 'show'])->name('site.categoria');

// PÃ¡gina offline (PWA)
Route::view('/offline', 'offline')->name('offline');

// PolÃ­tica/Privacidade
Route::view('/politica-privacidade', 'site.politicas')->name('site.politicas');



Route::get('/semtur', [SiteSecretariaController::class, 'show'])
    ->name('site.semtur');


// Nova URL canÃ´nica (sem "semtur")
Route::get('/secretaria', [SiteSecretariaController::class, 'show'])
    ->name('site.secretaria');




Route::get('/aviso/ativo', [AvisoFeedController::class, 'ativo'])->name('site.aviso.ativo');

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])
    ->name('google.redirect.localized');

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
    ->name('google.callback.localized');

Route::get('/ig-img', function (\Illuminate\Http\Request $r) {
    $u = $r->query('u');
    if (!$u || !Str::startsWith($u, ['http://','https://'])) {
        abort(400,'bad url');
    }

    $host = strtolower((string) parse_url($u, PHP_URL_HOST));
    $allowedHosts = [
        'cdninstagram.com',
        'fbcdn.net',
        'instagram.com',
    ];

    $isAllowedHost = $host !== '' && collect($allowedHosts)->contains(function (string $allowed) use ($host) {
        return $host === $allowed || Str::endsWith($host, '.'.$allowed);
    });

    if (! $isAllowedHost) {
        abort(403, 'host not allowed');
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
        if (! Str::startsWith(strtolower($ctype), 'image/')) {
            abort(415, 'unsupported content type');
        }

        // cache pÃºblico por 6h
        return response($resp->body(), 200)
            ->header('Content-Type', $ctype)
            ->header('Cache-Control', 'public, max-age=21600');
    } catch (\Throwable $e) {
        abort(502,'proxy fail');
    }
})->name('proxy.ig');

Route::get('/eventos', [EventoPublicController::class,'index'])->name('eventos.index');
Route::get('/eventos/{slug}/{ano?}', [EventoPublicController::class,'show'])
    ->whereNumber('ano')
    ->name('eventos.show');

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

Route::middleware('auth')->get('/profile', function () {
    $user = auth()->user();

    if ($user && method_exists($user, 'hasRole') && $user->hasRole('Cidadao')) {
        return redirect()->to(localized_route('site.perfil.index'));
    }

    return redirect()->route('profile.edit');
})->name('profile.localized');
    });


// =========================
// DASHBOARD / AUTENTICADOS
// =========================
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $u = auth()->user();

        if ($u->hasRole('Admin'))       return redirect()->route('admin.dashboard');
        if ($u->hasRole('Coordenador')) return redirect()->route('coordenador.dashboard');

        // TÃ©cnico: manda para o 1Âº mÃ³dulo permitido
        if ($u->hasRole('Tecnico')) {
            $preferencias = [
                ['perm' => 'pontos.view',           'route' => 'coordenador.pontos.index'],
                ['perm' => 'empresas.view',         'route' => 'coordenador.empresas.index'],
                ['perm' => 'categorias.view',       'route' => 'coordenador.categorias.index'],
                ['perm' => 'eventos.view',          'route' => 'coordenador.eventos.index'],
                ['perm' => 'banners_destaque.view', 'route' => 'coordenador.banners-destaque.index'],
                ['perm' => 'banners.view',          'route' => 'coordenador.banners.index'],
                ['perm' => 'avisos.view',           'route' => 'coordenador.avisos.index'],
                ['perm' => 'relatorios.view',       'route' => 'coordenador.coord.relatorios.index'],
                ['perm' => 'secretaria.edit',       'route' => 'coordenador.secretaria.edit'],
            ];
            foreach ($preferencias as $p) {
                if ($u->can($p['perm']) && Route::has($p['route'])) {
                    return redirect()->route($p['route']);
                }
            }
            return redirect()->route('coordenador.dashboard');
        }

        if ($u->hasRole('Cidadao')) return redirect()->to(localized_route('site.perfil.index'));

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
    Route::get('/dashboard', [AdminDash::class, 'index'])->name('dashboard');

    Route::prefix('config')->name('config.')->group(function () {
      Route::get('perfil',        [ConsoleProfile::class,'edit'])->name('perfil.edit');
      Route::put('perfil',        [ConsoleProfile::class,'update'])->name('perfil.update');
      Route::delete('perfil/foto',[ConsoleProfile::class,'destroyPhoto'])->name('perfil.foto.destroy');
    });

    Route::resource('usuarios', \App\Http\Controllers\Admin\UsuarioController::class)->except(['show']);

    Route::prefix('temas')->name('temas.')->group(function () {
        Route::get('/', [AdminThemeController::class, 'index'])
            ->middleware('permission:themes.view')
            ->name('index');
        Route::get('/create', [AdminThemeController::class, 'create'])
            ->middleware('permission:themes.create')
            ->name('create');
        Route::post('/', [AdminThemeController::class, 'store'])
            ->middleware('permission:themes.create')
            ->name('store');
        Route::post('/import', [AdminThemeController::class, 'import'])
            ->middleware('permission:themes.create')
            ->name('import');
        Route::post('/preview/clear', [AdminThemeController::class, 'clearPreview'])
            ->middleware('permission:themes.preview')
            ->name('preview.clear');
        Route::post('/{tema}/preview', [AdminThemeController::class, 'preview'])
            ->middleware('permission:themes.preview')
            ->name('preview');
        Route::get('/{tema}/edit', [AdminThemeController::class, 'edit'])
            ->middleware('permission:themes.edit')
            ->name('edit');
        Route::get('/{tema}/export', [AdminThemeController::class, 'export'])
            ->middleware('permission:themes.view')
            ->name('export');
        Route::put('/{tema}', [AdminThemeController::class, 'update'])
            ->middleware('permission:themes.edit')
            ->name('update');
        Route::patch('/{tema}/activate', [AdminThemeController::class, 'activate'])
            ->middleware('permission:themes.activate')
            ->name('activate');
        Route::patch('/{tema}/activate-site', [AdminThemeController::class, 'activateSite'])
            ->middleware('permission:themes.activate')
            ->name('activate-site');
        Route::patch('/{tema}/activate-auth', [AdminThemeController::class, 'activateAuth'])
            ->middleware('permission:themes.activate')
            ->name('activate-auth');
        Route::patch('/restore-default', [AdminThemeController::class, 'restoreDefault'])
            ->middleware('permission:themes.activate')
            ->name('restore-default');
        Route::patch('/restore-default-site', [AdminThemeController::class, 'restoreDefaultSite'])
            ->middleware('permission:themes.activate')
            ->name('restore-default-site');
        Route::patch('/restore-default-auth', [AdminThemeController::class, 'restoreDefaultAuth'])
            ->middleware('permission:themes.activate')
            ->name('restore-default-auth');
        Route::patch('/{tema}/archive', [AdminThemeController::class, 'archive'])
            ->middleware('permission:themes.activate')
            ->name('archive');
        Route::delete('/{tema}', [AdminThemeController::class, 'destroy'])
            ->middleware('permission:themes.archive')
            ->name('destroy');
    });


    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [AdminBackupController::class, 'index'])->name('index');
        Route::post('/generate', [AdminBackupController::class, 'generate'])->name('generate');
        Route::post('/import-package', [AdminBackupController::class, 'importPackage'])->name('import-package');
        Route::post('/download', [AdminBackupController::class, 'download'])->name('download');
        Route::post('/push-remote', [AdminBackupController::class, 'pushRemote'])->name('push-remote');
        Route::post('/pull-remote', [AdminBackupController::class, 'pullRemote'])->name('pull-remote');
        Route::delete('/local', [AdminBackupController::class, 'destroyLocal'])->name('destroy-local');
        Route::delete('/remote', [AdminBackupController::class, 'destroyRemote'])->name('destroy-remote');
        Route::post('/test-remote', [AdminBackupController::class, 'testRemote'])->name('test-remote');
        Route::post('/audit-media', [AdminBackupController::class, 'auditMedia'])->name('audit-media');
        Route::post('/prune-safe', [AdminBackupController::class, 'pruneSafe'])->name('prune-safe');
        Route::put('/remote-config', [AdminBackupController::class, 'updateRemoteConfig'])->name('remote-config.update');
    });
  });

// =========================
/* COORDENADOR */
// =========================
Route::middleware(['auth','role:Coordenador|Tecnico','coordenador.permission'])
    ->prefix('coordenador')->name('coordenador.')
    ->group(function () {


        Route::get('/dashboard', [CoordDash::class,'index'])->name('dashboard');

        Route::prefix('config')->name('config.')->group(function () {
            Route::get('perfil',       [ConsoleProfile::class,'edit'])->name('perfil.edit');
            Route::put('perfil',       [ConsoleProfile::class,'update'])->name('perfil.update');
            Route::delete('perfil/foto',[ConsoleProfile::class,'destroyPhoto'])->name('perfil.foto.destroy');
        });

        Route::middleware(['role:Coordenador', 'permission:themes.view'])
            ->prefix('temas')
            ->name('temas.')
            ->group(function () {
                Route::get('/', [ThemeExecutionController::class, 'index'])->name('index');
                Route::post('/preview-console/clear', [ThemeExecutionController::class, 'clearPreview'])
                    ->middleware('permission:themes.preview')
                    ->name('preview-console.clear');
                Route::post('/{tema}/preview-console', [ThemeExecutionController::class, 'previewConsole'])
                    ->middleware('permission:themes.preview')
                    ->name('preview-console');
                Route::patch('/{tema}/activate-console', [ThemeExecutionController::class, 'activateConsole'])
                    ->middleware('permission:themes.execute.console')
                    ->name('activate-console');
                Route::patch('/restore-default-console', [ThemeExecutionController::class, 'restoreConsoleDefault'])
                    ->middleware('permission:themes.execute.console')
                    ->name('restore-default-console');
                Route::patch('/{tema}/activate-site', [ThemeExecutionController::class, 'activateSite'])
                    ->middleware('permission:themes.execute.site')
                    ->name('activate-site');
                Route::patch('/restore-default-site', [ThemeExecutionController::class, 'restoreSiteDefault'])
                    ->middleware('permission:themes.execute.site')
                    ->name('restore-default-site');
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

        // mÃ­dia (novo + aliases legados)
        Route::delete('empresas/{empresa}/capa',   [CoordEmpresaController::class,'removerCapa'])->name('empresas.capa.remover');
        Route::delete('empresas/{empresa}/perfil', [CoordEmpresaController::class,'removerPerfil'])->name('empresas.perfil.remover');
        Route::delete('empresas/{empresa}/remover-capa',   [CoordEmpresaController::class,'removerCapa'])->name('empresas.removerCapa');
        Route::delete('empresas/{empresa}/remover-perfil', [CoordEmpresaController::class,'removerPerfil'])->name('empresas.removerPerfil');

        // recomendaÃ§Ãµes (empresas)
        Route::post('empresas/{empresa}/recomendar',         [CoordEmpresaController::class,'recomendar'])->name('empresas.recomendar');
        Route::delete('empresas/{empresa}/recomendar',       [CoordEmpresaController::class,'removerRecomendacao'])->name('empresas.recomendar.remover');
        Route::patch('empresas/recomendacoes/{rec}/ordem',   [CoordEmpresaController::class,'reordenarRecomendacao'])->name('empresas.recomendar.ordem');
        Route::patch('empresas/recomendacoes/{rec}/ordenar', [CoordEmpresaController::class,'reordenarRecomendacao'])->name('empresas.recomendar.ordenar');

        // Pontos turÃ­sticos
        Route::resource('pontos', PontoTuristicoController::class)->except(['show']);
        Route::patch('pontos/{ponto}/publicar', [PontoTuristicoController::class,'publicar'])->name('pontos.publicar');
        Route::patch('pontos/{ponto}/arquivar', [PontoTuristicoController::class,'arquivar'])->name('pontos.arquivar');
        Route::patch('pontos/{ponto}/rascunho', [PontoTuristicoController::class,'rascunho'])->name('pontos.rascunho');

        // capa do ponto (novo + alias legado)
        Route::delete('pontos/{ponto}/capa',           [PontoTuristicoController::class,'removerCapa'])->name('pontos.capa.remover');
        Route::delete('pontos/{ponto}/remover-capa',   [PontoTuristicoController::class,'removerCapa'])->name('pontos.removerCapa');


        // mÃ­dias do ponto
        Route::post('pontos/{ponto}/midias/imagens',    [PontoTuristicoController::class,'adicionarImagens'])->name('pontos.midias.imagens.add');
        Route::post('pontos/{ponto}/midias/video-link', [PontoTuristicoController::class,'adicionarVideoLink'])->name('pontos.midias.video.link');
        Route::post('pontos/{ponto}/midias/video-file', [PontoTuristicoController::class,'adicionarVideoFile'])->name('pontos.midias.video.file');
        Route::delete('pontos/midias/{midia}',          [PontoTuristicoController::class,'removerMidia'])->name('pontos.midias.destroy');

        // recomendaÃ§Ãµes (pontos)
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

        // EDIÃ‡Ã•ES
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

        Route::prefix('espacos-culturais/agendamentos')
            ->name('espacos-culturais.agendamentos.')
            ->group(function () {
                Route::get('/', [EspacoCulturalAgendamentoController::class, 'index'])->name('index');
                Route::get('/{agendamento}', [EspacoCulturalAgendamentoController::class, 'show'])->name('show');

                Route::patch('/{agendamento}/confirmar', [EspacoCulturalAgendamentoController::class, 'confirmar'])->name('confirmar');
                Route::patch('/{agendamento}/cancelar', [EspacoCulturalAgendamentoController::class, 'cancelar'])->name('cancelar');
                Route::patch('/{agendamento}/concluir', [EspacoCulturalAgendamentoController::class, 'concluir'])->name('concluir');
                Route::patch('/{agendamento}/atribuir-tecnico', [EspacoCulturalAgendamentoController::class, 'atribuirTecnico'])->name('atribuir-tecnico');
                Route::patch('/{agendamento}/observacao-interna', [EspacoCulturalAgendamentoController::class, 'observacaoInterna'])->name('observacao-interna');
            });

        //ESPAÃ‡O CULTURAL
        Route::resource('espacos-culturais', EspacoCulturalController::class)
            ->parameters(['espacos-culturais' => 'espaco'])
            ->except(['show']);

        //ONDE COMER
        Route::get('/onde-comer', [CoordOndeComerController::class, 'edit'])
            ->name('onde_comer.edit');

        Route::put('/onde-comer', [CoordOndeComerController::class, 'update'])
            ->name('onde_comer.update');


        // GALERIA
        Route::get('edicoes/{edicao}/midias',                     [EventoController::class,'midiasIndex'])->name('edicoes.midias.index');
        Route::post('edicoes/{edicao}/midias',                    [EventoController::class,'midiasStore'])->name('edicoes.midias.store');
        Route::delete('midias/{midia}',                           [EventoController::class,'midiasDestroy'])->name('midias.destroy');
        Route::post('edicoes/{edicao}/midias/reordenar',          [EventoController::class,'midiasReordenar'])->name('edicoes.midias.reordenar');

        Route::get('/secretaria', [SecretariaController::class, 'edit'])
          ->name('secretaria.edit');

        Route::put('/secretaria', [SecretariaController::class, 'update'])
            ->name('secretaria.update');

        //ONDE FICAR
        Route::get('/onde-ficar', [CoordOndeFicarController::class, 'edit'])
            ->name('onde_ficar.edit');

        Route::put('/onde-ficar', [CoordOndeFicarController::class, 'update'])
            ->name('onde_ficar.update');


        // Roteiros
        Route::resource('roteiros', CoordRoteiroController::class)->except(['show']);
        Route::patch('roteiros/{roteiro}/publicar', [CoordRoteiroController::class, 'publicar'])->name('roteiros.publicar');
        Route::patch('roteiros/{roteiro}/arquivar', [CoordRoteiroController::class, 'arquivar'])->name('roteiros.arquivar');
        Route::patch('roteiros/{roteiro}/rascunho', [CoordRoteiroController::class, 'rascunho'])->name('roteiros.rascunho');

        // Guias e Revistas
        Route::resource('guias', CoordGuiaRevistaController::class)
            ->parameters(['guias' => 'guia'])
            ->except(['show']);

        Route::patch('guias/{guia}/publicar', [CoordGuiaRevistaController::class, 'publicar'])
            ->name('guias.publicar');

        Route::patch('guias/{guia}/arquivar', [CoordGuiaRevistaController::class, 'arquivar'])
            ->name('guias.arquivar');

        Route::patch('guias/{guia}/rascunho', [CoordGuiaRevistaController::class, 'rascunho'])
            ->name('guias.rascunho');

        //VÃDEOS
        Route::resource('videos', CoordVideoController::class)
            ->parameters(['videos' => 'video'])
            ->except(['show']);

        Route::patch('videos/{video}/publicar', [CoordVideoController::class, 'publicar'])
            ->name('videos.publicar');

        Route::patch('videos/{video}/arquivar', [CoordVideoController::class, 'arquivar'])
            ->name('videos.arquivar');

        Route::patch('videos/{video}/rascunho', [CoordVideoController::class, 'rascunho'])
            ->name('videos.rascunho');

        // Jogos IndÃ­genas
        Route::resource('jogos-indigenas', CoordJogosIndigenasController::class)
            ->parameters(['jogos-indigenas' => 'jogosIndigena'])
            ->except(['show']);

        Route::prefix('jogos-indigenas/{jogosIndigena}/edicoes')
            ->name('jogos-indigenas.edicoes.')
            ->group(function () {
                Route::get('/', [CoordJogosIndigenasEdicaoController::class, 'index'])->name('index');
                Route::get('/create', [CoordJogosIndigenasEdicaoController::class, 'create'])->name('create');
                Route::post('/', [CoordJogosIndigenasEdicaoController::class, 'store'])->name('store');
            });

        Route::prefix('jogos-indigenas/{jogosIndigena}/edicoes/{edicao}')
            ->name('jogos-indigenas.edicoes.')
            ->group(function () {
                Route::get('/edit', [CoordJogosIndigenasEdicaoController::class, 'edit'])->name('edit');
                Route::put('/', [CoordJogosIndigenasEdicaoController::class, 'update'])->name('update');
                Route::delete('/', [CoordJogosIndigenasEdicaoController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('jogos-indigenas/{jogosIndigena}/edicoes/{edicao}/fotos')
            ->name('jogos-indigenas.edicoes.fotos.')
            ->group(function () {
                Route::get('/', [CoordJogosIndigenasEdicaoFotoController::class, 'index'])->name('index');
                Route::get('/create', [CoordJogosIndigenasEdicaoFotoController::class, 'create'])->name('create');
                Route::post('/', [CoordJogosIndigenasEdicaoFotoController::class, 'store'])->name('store');
                Route::get('/{foto}/edit', [CoordJogosIndigenasEdicaoFotoController::class, 'edit'])->name('edit');
                Route::put('/{foto}', [CoordJogosIndigenasEdicaoFotoController::class, 'update'])->name('update');
                Route::delete('/{foto}', [CoordJogosIndigenasEdicaoFotoController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('jogos-indigenas/{jogosIndigena}/edicoes/{edicao}/videos')
            ->name('jogos-indigenas.edicoes.videos.')
            ->group(function () {
                Route::get('/', [CoordJogosIndigenasEdicaoVideoController::class, 'index'])->name('index');
                Route::get('/create', [CoordJogosIndigenasEdicaoVideoController::class, 'create'])->name('create');
                Route::post('/', [CoordJogosIndigenasEdicaoVideoController::class, 'store'])->name('store');
                Route::get('/{video}/edit', [CoordJogosIndigenasEdicaoVideoController::class, 'edit'])->name('edit');
                Route::put('/{video}', [CoordJogosIndigenasEdicaoVideoController::class, 'update'])->name('update');
                Route::delete('/{video}', [CoordJogosIndigenasEdicaoVideoController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('jogos-indigenas/{jogosIndigena}/edicoes/{edicao}/patrocinadores')
            ->name('jogos-indigenas.edicoes.patrocinadores.')
            ->group(function () {
                Route::get('/', [CoordJogosIndigenasEdicaoPatrocinadorController::class, 'index'])->name('index');
                Route::get('/create', [CoordJogosIndigenasEdicaoPatrocinadorController::class, 'create'])->name('create');
                Route::post('/', [CoordJogosIndigenasEdicaoPatrocinadorController::class, 'store'])->name('store');
                Route::get('/{patrocinador}/edit', [CoordJogosIndigenasEdicaoPatrocinadorController::class, 'edit'])->name('edit');
                Route::put('/{patrocinador}', [CoordJogosIndigenasEdicaoPatrocinadorController::class, 'update'])->name('update');
                Route::delete('/{patrocinador}', [CoordJogosIndigenasEdicaoPatrocinadorController::class, 'destroy'])->name('destroy');
            });


        // RelatÃ³rios
        Route::resource('rota-do-cacau', CoordRotaDoCacauController::class)
            ->parameters(['rota-do-cacau' => 'rotaDoCacau'])
            ->except(['show']);

        Route::prefix('rota-do-cacau/{rotaDoCacau}/edicoes')
            ->name('rota-do-cacau.edicoes.')
            ->group(function () {
                Route::get('/', [CoordRotaDoCacauEdicaoController::class, 'index'])->name('index');
                Route::get('/create', [CoordRotaDoCacauEdicaoController::class, 'create'])->name('create');
                Route::post('/', [CoordRotaDoCacauEdicaoController::class, 'store'])->name('store');
            });

        Route::prefix('rota-do-cacau/{rotaDoCacau}/edicoes/{edicao}')
            ->name('rota-do-cacau.edicoes.')
            ->group(function () {
                Route::get('/edit', [CoordRotaDoCacauEdicaoController::class, 'edit'])->name('edit');
                Route::put('/', [CoordRotaDoCacauEdicaoController::class, 'update'])->name('update');
                Route::delete('/', [CoordRotaDoCacauEdicaoController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('rota-do-cacau/{rotaDoCacau}/edicoes/{edicao}/fotos')
            ->name('rota-do-cacau.edicoes.fotos.')
            ->group(function () {
                Route::get('/', [CoordRotaDoCacauEdicaoFotoController::class, 'index'])->name('index');
                Route::get('/create', [CoordRotaDoCacauEdicaoFotoController::class, 'create'])->name('create');
                Route::post('/', [CoordRotaDoCacauEdicaoFotoController::class, 'store'])->name('store');
                Route::get('/{foto}/edit', [CoordRotaDoCacauEdicaoFotoController::class, 'edit'])->name('edit');
                Route::put('/{foto}', [CoordRotaDoCacauEdicaoFotoController::class, 'update'])->name('update');
                Route::delete('/{foto}', [CoordRotaDoCacauEdicaoFotoController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('rota-do-cacau/{rotaDoCacau}/edicoes/{edicao}/videos')
            ->name('rota-do-cacau.edicoes.videos.')
            ->group(function () {
                Route::get('/', [CoordRotaDoCacauEdicaoVideoController::class, 'index'])->name('index');
                Route::get('/create', [CoordRotaDoCacauEdicaoVideoController::class, 'create'])->name('create');
                Route::post('/', [CoordRotaDoCacauEdicaoVideoController::class, 'store'])->name('store');
                Route::get('/{video}/edit', [CoordRotaDoCacauEdicaoVideoController::class, 'edit'])->name('edit');
                Route::put('/{video}', [CoordRotaDoCacauEdicaoVideoController::class, 'update'])->name('update');
                Route::delete('/{video}', [CoordRotaDoCacauEdicaoVideoController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('rota-do-cacau/{rotaDoCacau}/edicoes/{edicao}/patrocinadores')
            ->name('rota-do-cacau.edicoes.patrocinadores.')
            ->group(function () {
                Route::get('/', [CoordRotaDoCacauEdicaoPatrocinadorController::class, 'index'])->name('index');
                Route::get('/create', [CoordRotaDoCacauEdicaoPatrocinadorController::class, 'create'])->name('create');
                Route::post('/', [CoordRotaDoCacauEdicaoPatrocinadorController::class, 'store'])->name('store');
                Route::get('/{patrocinador}/edit', [CoordRotaDoCacauEdicaoPatrocinadorController::class, 'edit'])->name('edit');
                Route::put('/{patrocinador}', [CoordRotaDoCacauEdicaoPatrocinadorController::class, 'update'])->name('update');
                Route::delete('/{patrocinador}', [CoordRotaDoCacauEdicaoPatrocinadorController::class, 'destroy'])->name('destroy');
            });

        Route::get('/relatorios', [RelatorioController::class, 'index'])
            ->middleware('permission:relatorios.view')
            ->name('coord.relatorios.index');
        Route::get('/relatorios/exportar.csv', [RelatorioController::class, 'exportCsv'])
            ->middleware('permission:relatorios.view')
            ->name('coord.relatorios.csv');

    Route::middleware(['role:Coordenador', 'permission:tecnicos.manage'])
        ->prefix('tecnicos')
        ->name('tecnicos.')
        ->group(function () {

            Route::get('/',           [TecnicoController::class,'index'])->name('index');
            Route::get('/create',     [TecnicoController::class,'create'])->name('create');
            Route::post('/',          [TecnicoController::class,'store'])->name('store');
            Route::get('/{user}/edit',[TecnicoController::class,'edit'])->name('edit');
            Route::put('/{user}',     [TecnicoController::class,'update'])->name('update');
            Route::delete('/{user}',  [TecnicoController::class,'destroy'])->name('destroy');
            });


    });

Route::post('/console/cache/clear', [MaintenanceController::class, 'clear'])
    ->middleware(['auth', 'permission:console.cache.clear', 'throttle:3,1'])
    ->name('console.cache.clear');




    // TÃ‰CNICO (compatibilidade de URL)
    Route::middleware(['auth','role:Tecnico'])
        ->prefix('tecnico')->name('tecnico.')
        ->group(function () {

            // /tecnico/dashboard -> para o mÃ³dulo do coordenador que o tÃ©cnico pode ver
            Route::get('/dashboard', function () {
                $u = auth()->user();

                $targets = [
                    ['perm' => 'pontos.view',   'route' => 'coordenador.pontos.index'],
                    ['perm' => 'roteiros.view', 'route' => 'coordenador.roteiros.index'],
                    ['perm' => 'guias.view',    'route' => 'coordenador.guias.index'],
                    ['perm' => 'videos.view', 'route' => 'coordenador.videos.index'],
                ];

                foreach ($targets as $target) {
                    if ($u->can($target['perm']) && Route::has($target['route'])) {
                        return redirect()->route($target['route']);
                    }
                }

                return redirect()->route('dashboard');
            })->name('dashboard');

            // (opcional) manter URL /tecnico/config/perfil funcionando, apontando para a tela Ãºnica
            Route::get('config/perfil', function () {
                return redirect()->route('coordenador.config.perfil.edit');
            })->name('config.perfil.edit');
        });



require __DIR__.'/auth.php';

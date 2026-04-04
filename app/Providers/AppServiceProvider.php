<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Empresa;
use App\Models\Catalogo\EmpresaRecomendacao;
use App\Models\Catalogo\PontoMidia;
use App\Models\Catalogo\PontoRecomendacao;
use App\Models\Catalogo\PontoTuristico;
use App\Models\Catalogo\Roteiro;
use App\Models\Catalogo\RoteiroEmpresa;
use App\Models\Catalogo\RoteiroEtapa;
use App\Models\Catalogo\RoteiroEtapaPonto;
use App\Models\Conteudo\Aviso;
use App\Models\Conteudo\Banner;
use App\Models\Conteudo\BannerDestaque;
use App\Observers\SiteSyncObserver;
use App\Services\ConteudoSiteTranslationEngine;
use App\Services\ConteudoSiteTranslationManager;
use App\Services\NullConteudoSiteTranslationEngine;
use App\Services\OpenAiConteudoSiteTranslationEngine;
use App\Services\ThemeManager;
use App\Services\ThemeResolver;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Support/helpers.php');
        require_once app_path('Support/theme_helpers.php');

        $this->app->singleton(ThemeResolver::class, fn () => new ThemeResolver());
        $this->app->singleton(ThemeManager::class, fn ($app) => new ThemeManager($app->make(ThemeResolver::class)));
        $this->app->singleton(ConteudoSiteTranslationEngine::class, function ($app) {
            $provider = (string) config('services.site_translation.provider', 'null');

            return match ($provider) {
                'openai' => new OpenAiConteudoSiteTranslationEngine(),
                default => new NullConteudoSiteTranslationEngine(),
            };
        });
        $this->app->singleton(
            ConteudoSiteTranslationManager::class,
            fn ($app) => new ConteudoSiteTranslationManager($app->make(ConteudoSiteTranslationEngine::class))
        );
    }

    public function boot(): void
    {
        URL::defaults([
            'locale' => route_locale(),
        ]);

        $resolveLocale = static function (?string $preferredLocale = null): string {
            return route_locale($preferredLocale);
        };

        ResetPassword::createUrlUsing(function ($user, string $token) use ($resolveLocale) {
            return route('password.reset', [
                'locale' => $resolveLocale(),
                'token' => $token,
                'email' => $user->email,
            ]);
        });

        VerifyEmail::createUrlUsing(function ($notifiable) use ($resolveLocale) {
            return URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'locale' => $resolveLocale(),
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        });

        Categoria::observe(SiteSyncObserver::class);
        Empresa::observe(SiteSyncObserver::class);
        PontoTuristico::observe(SiteSyncObserver::class);
        PontoMidia::observe(SiteSyncObserver::class);
        PontoRecomendacao::observe(SiteSyncObserver::class);
        EmpresaRecomendacao::observe(SiteSyncObserver::class);

        Banner::observe(SiteSyncObserver::class);
        BannerDestaque::observe(SiteSyncObserver::class);
        Aviso::observe(SiteSyncObserver::class);

        View::composer('*', function ($view) {
            $aviso = null;
            $themePayload = [
                'context' => null,
                'theme' => null,
                'activeTheme' => null,
                'previewTheme' => null,
                'isPreview' => false,
                'dataTheme' => 'default',
                'cssVariables' => [],
                'assets' => [],
                'hasCustomConsoleTheme' => false,
            ];

            if (Schema::hasTable('avisos')) {
                $aviso = Cache::remember('aviso:ativo', 60, function () {
                    return Aviso::publicados()
                        ->janelaAtiva()
                        ->latest('updated_at')
                        ->first();
                });
            }

            if (Schema::hasTable('themes') && Schema::hasTable('system_settings')) {
                $themePayload = app(ThemeResolver::class)->payload(auth()->user());
            }

            $view->with([
                'aviso' => $aviso,
                'resolvedThemeContext' => $themePayload['context'] ?? null,
                'resolvedTheme' => $themePayload['theme'],
                'resolvedActiveTheme' => $themePayload['activeTheme'],
                'resolvedPreviewTheme' => $themePayload['previewTheme'],
                'resolvedThemeIsPreview' => $themePayload['isPreview'],
                'resolvedThemeDataTheme' => $themePayload['dataTheme'],
                'resolvedThemeCssVariables' => $themePayload['cssVariables'],
                'resolvedThemeAssets' => $themePayload['assets'],
                'resolvedThemeHasCustomConsoleTheme' => $themePayload['hasCustomConsoleTheme'] ?? false,
            ]);
        });

        Roteiro::observe(SiteSyncObserver::class);
        RoteiroEtapa::observe(SiteSyncObserver::class);
        RoteiroEtapaPonto::observe(SiteSyncObserver::class);
        RoteiroEmpresa::observe(SiteSyncObserver::class);
    }
}

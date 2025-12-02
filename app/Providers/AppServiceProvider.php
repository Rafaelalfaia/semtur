<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\SiteSyncObserver;
use App\Models\Catalogo\{Categoria, Empresa, PontoTuristico};
use App\Models\Catalogo\{PontoMidia, PontoRecomendacao};
use App\Models\Catalogo\{EmpresaRecomendacao};
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\Conteudo\Aviso;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Categoria::observe(SiteSyncObserver::class);
        Empresa::observe(SiteSyncObserver::class);
        PontoTuristico::observe(SiteSyncObserver::class);
        PontoMidia::observe(SiteSyncObserver::class);
        PontoRecomendacao::observe(SiteSyncObserver::class);
        EmpresaRecomendacao::observe(SiteSyncObserver::class);

        View::composer('*', function ($view) {
            $aviso = null;

            if (Schema::hasTable('avisos')) {
                $aviso = Cache::remember('aviso:ativo', 60, function () {
                    return Aviso::publicados()
                        ->janelaAtiva()
                        ->latest('updated_at')
                        ->first();
                });
            }

            $view->with('aviso', $aviso);
        });
    }

}

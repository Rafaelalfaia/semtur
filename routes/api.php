<?php

use Illuminate\Support\Facades\Route;

// API Controllers
use App\Http\Controllers\Api\HomeApiController;
use App\Http\Controllers\Api\MapaApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Estas rotas são stateless (sem sessão/CSRF) e respondem JSON.
| O prefixo /api é aplicado automaticamente neste arquivo.
*/

Route::get('/home',                   [HomeApiController::class, 'index'])->name('api.home');
Route::get('/categorias',             [HomeApiController::class, 'categorias'])->name('api.categorias');
Route::get('/categorias/{slug}/feed', [HomeApiController::class, 'categoriaFeed'])->name('api.categoria.feed');
Route::get('/pontos/{id}',            [HomeApiController::class, 'ponto'])->whereNumber('id')->name('api.ponto');
Route::get('/empresas/{slug}',        [HomeApiController::class, 'empresa'])->name('api.empresa');

// Feed do mapa
Route::get('/mapa/feed', [MapaApiController::class, 'feed'])
    ->name('api.mapa.feed');

Route::get('/mapa/markers', [MapaApiController::class, 'markers'])
    ->name('api.mapa.markers');

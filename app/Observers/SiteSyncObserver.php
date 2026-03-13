<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class SiteSyncObserver
{
    /** Limpa os caches da Home/Explorar */
    protected function clearHome(): void
    {
        Cache::forget('home:categorias');
        Cache::forget('home:recomendados:pontos:q='.md5(''));
        Cache::forget('home:recomendados:mix:q='.md5(''));
        Cache::forget('home:recomendados:mix:q='.md5(''));
        Cache::forget('home:pontos:q='.md5('').':l=6');
        Cache::forget('home:empresas:hoteis:q='.md5('').':l=6');
        Cache::forget('home:empresas:turismo:q='.md5('').':l=6');
        // (se usar Redis com tags, depois trocamos por Cache::tags(['home'])->flush())
    }

    public function created($model)  { $this->clearHome(); }
    public function updated($model)  { $this->clearHome(); }
    public function deleted($model)  { $this->clearHome(); }
    public function restored($model) { $this->clearHome(); }
}

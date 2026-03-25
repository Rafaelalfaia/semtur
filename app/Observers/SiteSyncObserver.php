<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class SiteSyncObserver
{
    protected function clearHome(): void
    {
        $keys = [
            'home:categorias',
            'home:recomendados:pontos:q=' . md5(''),
            'home:recomendados:mix:q=' . md5(''),
            'home:pontos:q=' . md5('') . ':l=6',
            'home:empresas:hoteis:q=' . md5('') . ':l=6',
            'home:empresas:turismo:q=' . md5('') . ':l=6',

            'home:banner',
            'home:banners_normais',
            'home:banner_topo',
            'home:banners_destaque',
            'home:banner_principal',

            'aviso:ativo',
        ];

        foreach (array_unique($keys) as $key) {
            Cache::forget($key);
        }
    }

    public function created($model)
    {
        $this->clearHome();
    }

    public function updated($model)
    {
        $this->clearHome();
    }

    public function deleted($model)
    {
        $this->clearHome();
    }

    public function restored($model)
    {
        $this->clearHome();
    }
}

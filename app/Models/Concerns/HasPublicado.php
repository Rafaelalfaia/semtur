<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasPublicado
{
    // Masculino (padrão)
    public function scopePublicados(Builder $q): Builder
    {
        return $q->where('status', 'publicado')
                 ->where(function ($w) {
                     $w->whereNull('published_at')
                       ->orWhere('published_at', '<=', now());
                 });
    }

    // Feminino (alias) — para chamadas como ->publicadas()
    public function scopePublicadas(Builder $q): Builder
    {
        return $this->scopePublicados($q);
    }

    // Utilitário comum que aparece no teu código (ordenado por ordem, depois nome)
    public function scopeOrdenado(Builder $q): Builder
    {
        return $q->orderBy('ordem')->orderBy('nome');
    }
}

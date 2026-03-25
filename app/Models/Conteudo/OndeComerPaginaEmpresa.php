<?php

namespace App\Models\Conteudo;

use App\Models\Catalogo\Empresa;
use Illuminate\Database\Eloquent\Model;

class OndeComerPaginaEmpresa extends Model
{
    protected $table = 'onde_comer_pagina_empresas';

    protected $fillable = [
        'onde_comer_pagina_id',
        'empresa_id',
        'ordem',
        'destaque',
        'observacao_curta',
    ];

    protected $casts = [
        'destaque' => 'boolean',
    ];

    public function pagina()
    {
        return $this->belongsTo(OndeComerPagina::class, 'onde_comer_pagina_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}

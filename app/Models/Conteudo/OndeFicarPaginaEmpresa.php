<?php

namespace App\Models\Conteudo;

use App\Models\Catalogo\Empresa;
use Illuminate\Database\Eloquent\Model;

class OndeFicarPaginaEmpresa extends Model
{
    protected $table = 'onde_ficar_pagina_empresas';

    protected $fillable = [
        'onde_ficar_pagina_id',
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
        return $this->belongsTo(OndeFicarPagina::class, 'onde_ficar_pagina_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}

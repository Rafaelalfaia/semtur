<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Secretaria;
use App\Models\EquipeMembro;

class SecretariaController extends Controller
{
    public function show()
    {
        $sec = Secretaria::instance();
        $membros = EquipeMembro::publicados()->ordenados()->get();

        return view('site.semtur.show', compact('sec','membros'));
    }
}

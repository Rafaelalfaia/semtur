<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conteudo\Aviso;

class AvisoSeeder extends Seeder
{
    public function run(): void
    {
        Aviso::updateOrCreate(
            ['titulo' => 'Festival de Turismo de Altamira'],
            [
                'descricao'   => 'Participe do festival neste fim de semana. Programação cultural e passeios!',
                'whatsapp'    => '5593999998888',
                'imagem_path' => null, // faça upload depois via painel e salve path
                'status'      => 'publicado',
                'inicio_em'   => now()->subDay(),
                'fim_em'      => now()->addDays(10),
            ]
        );
    }
}

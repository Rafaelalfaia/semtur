<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Sempre rode as permissões/papéis
        $this->call(PermissionsSeeder::class);
        $this->call(ThemeSeeder::class);

        // 2) (Opcional) Crie/garanta um admin padrão de forma idempotente
        // Troque o e-mail/senha via .env se quiser:
        // SEED_ADMIN_EMAIL=admin@visitaltamira.com.br
        // SEED_ADMIN_PASSWORD=TroqueEstaSenha123!
        $email = env('SEED_ADMIN_EMAIL', 'admin@visitaltamira.com.br');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => 'Administrador',
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'TroqueEstaSenha123!')),
            ]
        );

        // Dê o papel Admin se ainda não tiver
        if (method_exists($admin, 'assignRole') && !$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        // ⚠️ Importante: REMOVA/COMENTE linhas padrão como:
        // User::factory()->create(['name'=>'Test User','email'=>'test@example.com']);
        // e qualquer outra factory que fixe e-mails repetidos.
    }
}

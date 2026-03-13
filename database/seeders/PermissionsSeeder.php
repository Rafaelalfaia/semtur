<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Guard padrão
        $guard = config('auth.defaults.guard', 'web');

        // ------------------------------------------------------------
        // 1) Catálogo de permissões (organizado por grupos/recursos)
        // ------------------------------------------------------------
        // Obs.: nomes seguem o padrão <grupo>[.<subgrupo>].<ação>
        $groups = [
            // Conteúdo principal
            'categorias'        => ['view','create','update','delete','publicar','arquivar','rascunho'],
            'empresas'          => ['view','create','update','delete','publicar','arquivar','rascunho'],
            'pontos'            => ['view','create','update','delete','publicar','arquivar','rascunho'],

            // Banners
            'banners'           => ['view','manage'],
            'banners_destaque'  => ['view','manage','reordenar','toggle'],

            // Avisos
            'avisos'            => ['view','manage','publicar','arquivar'],

            // Eventos + sub-recursos
            'eventos'               => ['view','manage'],
            'eventos.edicoes'       => ['manage'],
            'eventos.atrativos'     => ['manage','reordenar'],
            'eventos.midias'        => ['manage','reordenar'],

            // Institucional
            'secretaria'        => ['edit'],
            'equipe'            => ['manage'],

            // Extras
            'relatorios'        => ['view'],
            'console.cache'     => ['clear'],

            // Administração
            'usuarios'          => ['manage'],

            // Gestão de técnicos pelo coordenador
            'tecnicos'          => ['manage'],

            //ESPAÇO CULTURAL
            'espacos_culturais' => ['view','create','update','delete','publicar','arquivar','rascunho'],
        ];

        // Flatten: gera lista "grupo.acao" (ou "grupo.sub.acao")
        $catalog = [];
        foreach ($groups as $group => $actions) {
            foreach ($actions as $action) {
                $catalog[] = "{$group}.{$action}";
            }
        }

        // ------------------------------------------------------------
        // 2) Criar/garantir permissões (idempotente)
        // ------------------------------------------------------------
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($catalog as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard],
                [] // sem attrs extras
            );
        }

        // ------------------------------------------------------------
        // 3) Criar/garantir papéis
        // ------------------------------------------------------------
        $roleAdmin       = Role::firstOrCreate(['name' => 'Admin',       'guard_name' => $guard]);
        $roleCoordenador = Role::firstOrCreate(['name' => 'Coordenador', 'guard_name' => $guard]);
        $roleTecnico     = Role::firstOrCreate(['name' => 'Tecnico',     'guard_name' => $guard]);
        Role::firstOrCreate(['name' => 'Cidadao',    'guard_name' => $guard]); // sem permissões

        // Names de todas as permissões
        $allPerms = Permission::pluck('name')->all();

        // Helper: filtra por prefixo(s)
        $byPrefixes = function (array $prefixes) use ($allPerms): array {
            $out = [];
            foreach ($allPerms as $perm) {
                foreach ($prefixes as $p) {
                    if (Str::startsWith($perm, $p)) {
                        $out[] = $perm;
                        break;
                    }
                }
            }
            return array_values(array_unique($out));
        };

        // ------------------------------------------------------------
        // 4) Baselines por papel
        // ------------------------------------------------------------

        // Admin: tudo
        $roleAdmin->syncPermissions($allPerms);

        // Coordenador: todo conteúdo editorial + relatorios + técnicos
        // (opcional) conceder secretaria.* via .env => SEED_COORD_SECRETARIA=true
        $coordPrefixes = [
            'categorias.',
            'empresas.',
            'pontos.',
            'banners.',
            'banners_destaque.',
            'avisos.',
            'eventos.',          // cobre também sub-recursos (edicoes/atrativos/midias)
            'relatorios.',
            'tecnicos.',
        ];
        if (filter_var(env('SEED_COORD_SECRETARIA', false), FILTER_VALIDATE_BOOLEAN)) {
            $coordPrefixes[] = 'secretaria.';
        }
        // (por padrão, NÃO inclui 'usuarios.' nem 'console.cache.' para coordenador)
        $roleCoordenador->syncPermissions($byPrefixes($coordPrefixes));

        // Técnico: conjunto enxuto para produção de conteúdo
        // (sem publicar/arquivar/excluir por padrão)
        $tecnicoAllow = [
            'empresas.view','empresas.create','empresas.update',
            'pontos.view','pontos.create','pontos.update',
            'banners.view',
            'banners_destaque.view',
            'eventos.view',
            'eventos.edicoes.manage',
            'eventos.midias.manage',
            'espacos_culturais.view',
            'espacos_culturais.create',
            'espacos_culturais.update',
        ];
        $roleTecnico->syncPermissions($tecnicoAllow);
        // ------------------------------------------------------------
        // 5) Finalização
        // ------------------------------------------------------------
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (app()->runningInConsole() && method_exists($this, 'command')) {
            $this->command->info('✔ Permissões criadas/garantidas: '.count($catalog));
            $this->command->info('✔ Papéis sincronizados: Admin (tudo), Coordenador (conteúdo/relatórios/técnicos), Técnico (produção).');
            $this->command->info('ℹ Use SEED_COORD_SECRETARIA=true no .env para liberar "secretaria.*" ao Coordenador.');
        }
    }
}

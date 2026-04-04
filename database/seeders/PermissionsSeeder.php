<?php

namespace Database\Seeders;

use App\Models\User;
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
            // Modulo de temas: governanca administrativa do tema institucional
            'themes'            => ['view','create','edit','preview','activate','archive','execute.console','execute.site'],

            // Administração
            'usuarios'          => ['manage'],

            // Gestão de técnicos pelo coordenador
            'tecnicos'          => ['manage'],
            'site'              => ['manage'],

            //ESPAÇO CULTURAL
            'espacos_culturais' => ['view','create','update','delete','publicar','arquivar','rascunho'],

            'roteiros' => ['view','create','update','delete','publicar','arquivar','rascunho'],

            'onde_comer' => ['view', 'update', 'publicar', 'arquivar', 'rascunho'],
            'onde_ficar' => ['view', 'update', 'publicar', 'arquivar', 'rascunho'],
            'guias' => ['view','create','update','delete','publicar','arquivar','rascunho'],
            'videos' => ['view','create','update','delete','publicar','arquivar','rascunho'],
            'cursos' => ['view','create','update','delete','publicar','arquivar','rascunho'],
            'cursos.modulos' => ['view','create','update','delete','publicar','arquivar','rascunho'],
            'cursos.aulas' => ['view','create','update','delete','publicar','arquivar','rascunho'],
            'jogos_indigenas' => ['view','create','update','delete','publicar','arquivar','rascunho'],
            'jogos_indigenas.edicoes' => ['view','create','update','delete','publicar','arquivar','rascunho'],
            'jogos_indigenas.edicoes.fotos' => ['view','create','update','delete'],
            'jogos_indigenas.edicoes.videos' => ['view','create','update','delete'],
            'jogos_indigenas.edicoes.patrocinadores' => ['view','create','update','delete'],
            'rota_do_cacau' => ['view','create','update','delete','publicar','arquivar','rascunho'],
            'rota_do_cacau.edicoes' => ['view','create','update','delete','publicar','arquivar','rascunho'],
            'rota_do_cacau.edicoes.fotos' => ['view','create','update','delete'],
            'rota_do_cacau.edicoes.videos' => ['view','create','update','delete'],
            'rota_do_cacau.edicoes.patrocinadores' => ['view','create','update','delete'],
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

        // Coordenador: sem baseline por role.
        // O acesso real será cedido pelo Admin via permissões diretas.
        $modulosInstitucionaisPerms = $byPrefixes([
            'banners',
            'banners_destaque',
            'avisos',
            'categorias',
            'empresas',
            'pontos',
            'eventos',
            'espacos_culturais',
            'roteiros',
            'onde_comer',
            'onde_ficar',
            'guias',
            'videos',
            'cursos',
            'jogos_indigenas',
            'rota_do_cacau',
            'relatorios',
            'secretaria',
            'equipe',
            'tecnicos',
        ]);
        $modulosInstitucionaisPerms = array_values(array_unique(array_merge(
            $modulosInstitucionaisPerms,
            [
                'themes.view',
                'themes.preview',
                'themes.execute.console',
                'themes.execute.site',
            ]
        )));
        $roleCoordenador->syncPermissions($modulosInstitucionaisPerms);

        // O seeder nao injeta baseline como permissao direta no usuario.
        // Assim, o role continua sendo a fonte do baseline e as permissoes
        // diretas permanecem reservadas para extras/customizacoes.
        User::role('Coordenador')->get()->each(function (User $user) use ($modulosInstitucionaisPerms) {
            $customDirect = $user->getDirectPermissions()
                ->pluck('name')
                ->reject(fn (string $permission) => in_array($permission, $modulosInstitucionaisPerms, true))
                ->values()
                ->all();

            $user->syncPermissions($customDirect);
            $user->syncTecnicosDelegatedPermissions();
        });


        // Técnico: sem baseline por role.
        // O acesso real será cedido pelo Coordenador via permissões diretas.
        $roleTecnico->syncPermissions([]);


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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ThemeResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(ThemeResolver $themeResolver)
    {
        $moduleDefinitions = $this->contentModules();
        $activeTheme = $themeResolver->activeTheme();

        $totalConteudo = collect($moduleDefinitions)
            ->sum(fn (array $module) => $this->safeCount($module['table']));

        $publicados = collect($moduleDefinitions)
            ->sum(fn (array $module) => $this->safeStatusCount($module['table'], ['publicado']));

        $rascunhos = collect($moduleDefinitions)
            ->sum(fn (array $module) => $this->safeStatusCount($module['table'], ['rascunho']));

        $usuarios = $this->safeCount('users');

        $metricas = [
            [
                'label'  => 'Usuários do sistema',
                'value'  => number_format($usuarios),
                'helper' => 'Contas cadastradas na plataforma',
                'badge'  => 'Geral',
                'tone'   => 'primary',
            ],
            [
                'label'  => 'Coordenadores',
                'value'  => number_format($this->safeRoleCount('Coordenador')),
                'helper' => 'Gestores com acesso operacional',
                'badge'  => 'Hierarquia',
                'tone'   => 'success',
            ],
            [
                'label'  => 'Técnicos',
                'value'  => number_format($this->safeRoleCount('Tecnico')),
                'helper' => 'Perfis subordinados ativos',
                'badge'  => 'Operação',
                'tone'   => 'warning',
            ],
            [
                'label'  => 'Itens de conteúdo',
                'value'  => number_format($totalConteudo),
                'helper' => 'Soma dos módulos editoriais principais',
                'badge'  => 'Catálogo',
                'tone'   => 'primary',
            ],
        ];

        $resumo = [
            'publicados'       => number_format($publicados),
            'rascunhos'        => number_format($rascunhos),
            'modulos'          => number_format($this->modulosAtivos()),
            'usuarios'         => number_format($usuarios),
            'conteudos'        => number_format($totalConteudo),
            'taxa_publicacao'  => $this->formatPercent($publicados, $totalConteudo),
            'tema'             => $activeTheme?->name ?? 'Tema institucional',
        ];

        $modulos = $this->buildModuleOverview($moduleDefinitions);

        $recentes = array_values(array_filter(array_merge(
            $this->safeRecent('categorias', 'nome', 'Categoria'),
            $this->safeRecent('empresas', 'nome', 'Empresa'),
            $this->safeRecent('pontos_turisticos', 'nome', 'Ponto'),
            $this->safeRecent('eventos', 'nome', 'Evento'),
            $this->safeRecent('espacos_culturais', 'nome', 'Espaço cultural'),
            $this->safeRecent('guias_revistas', 'titulo', 'Guia/Revista'),
            $this->safeRecent('videos', 'titulo', 'Vídeo')
        )));

        usort($recentes, fn ($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
        $recentes = array_slice($recentes, 0, 7);

        $hierarquia = [
            [
                'label' => 'Admin',
                'value' => number_format($this->safeRoleCount('Admin')),
                'helper' => 'Camada estratégica do console',
                'badge_class' => 'ui-badge-primary',
            ],
            [
                'label' => 'Coordenador',
                'value' => number_format($this->safeRoleCount('Coordenador')),
                'helper' => 'Perfis com gestão de módulos',
                'badge_class' => 'ui-badge-success',
            ],
            [
                'label' => 'Técnico',
                'value' => number_format($this->safeRoleCount('Tecnico')),
                'helper' => 'Perfis operacionais vinculados',
                'badge_class' => 'ui-badge-warning',
            ],
        ];

        $atalhos = [
            [
                'label' => 'Gerenciar usuários',
                'route' => Route::has('admin.usuarios.index') ? route('admin.usuarios.index') : null,
            ],
            [
                'label' => 'Ver coordenadores',
                'route' => Route::has('admin.usuarios.index') ? route('admin.usuarios.index', ['role' => 'Coordenador']) : null,
            ],
            [
                'label' => 'Ver técnicos',
                'route' => Route::has('admin.usuarios.index') ? route('admin.usuarios.index', ['role' => 'Tecnico']) : null,
            ],
            [
                'label' => 'Temas do sistema',
                'route' => Route::has('admin.temas.index') ? route('admin.temas.index') : null,
            ],
        ];

        return view('admin.dashboard', [
            'metricas'   => $metricas,
            'resumo'     => $resumo,
            'modulos'    => $modulos,
            'recentes'   => $recentes,
            'hierarquia' => $hierarquia,
            'atalhos'    => $atalhos,
        ]);
    }

    private function contentModules(): array
    {
        return [
            ['nome' => 'Categorias',        'table' => 'categorias'],
            ['nome' => 'Empresas',          'table' => 'empresas'],
            ['nome' => 'Pontos turísticos', 'table' => 'pontos_turisticos'],
            ['nome' => 'Eventos',           'table' => 'eventos'],
            ['nome' => 'Museus e teatros',  'table' => 'espacos_culturais'],
            ['nome' => 'Roteiros',          'table' => 'roteiros'],
            ['nome' => 'Guias e revistas',  'table' => 'guias_revistas'],
            ['nome' => 'Vídeos',            'table' => 'videos'],
        ];
    }

    private function buildModuleOverview(array $moduleDefinitions): array
    {
        $modules = collect($moduleDefinitions)
            ->map(function (array $module) {
                return [
                    'nome'   => $module['nome'],
                    'table'  => $module['table'],
                    'total'  => $this->safeCount($module['table']),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $max = max(1, (int) $modules->max('total'));

        return $modules
            ->map(function (array $module) use ($max) {
                $total = (int) $module['total'];

                return [
                    'nome'    => $module['nome'],
                    'total'   => $total,
                    'percent' => $total > 0 ? max(10, (int) round(($total / $max) * 100)) : 0,
                    'status'  => $total > 0 ? 'ativo' : 'sem registros',
                ];
            })
            ->all();
    }

    private function modulosAtivos(): int
    {
        $tables = [
            'categorias',
            'empresas',
            'pontos_turisticos',
            'eventos',
            'espacos_culturais',
            'roteiros',
            'guias_revistas',
            'videos',
            'avisos',
            'banners',
        ];

        return collect($tables)
            ->filter(fn (string $table) => Schema::hasTable($table))
            ->count();
    }

    private function formatPercent(int $numerator, int $denominator): string
    {
        if ($denominator <= 0) {
            return '0%';
        }

        return number_format(($numerator / $denominator) * 100, 1, ',', '.') . '%';
    }

    private function safeCount(string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        try {
            return (int) DB::table($table)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function safeStatusCount(string $table, array $statuses): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'status')) {
            return 0;
        }

        try {
            return (int) DB::table($table)
                ->whereIn('status', $statuses)
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function safeRoleCount(string $role): int
    {
        try {
            return (int) User::role($role)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function safeRecent(string $table, string $titleColumn, string $tipo, int $limit = 2): array
    {
        if (
            ! Schema::hasTable($table) ||
            ! Schema::hasColumn($table, $titleColumn) ||
            ! Schema::hasColumn($table, 'created_at')
        ) {
            return [];
        }

        try {
            $query = DB::table($table)->select([$titleColumn, 'created_at']);

            if (Schema::hasColumn($table, 'status')) {
                $query->addSelect('status');
            }

            return $query
                ->latest('created_at')
                ->limit($limit)
                ->get()
                ->map(function ($item) use ($titleColumn, $tipo) {
                    return [
                        'tipo'       => $tipo,
                        'titulo'     => (string) ($item->{$titleColumn} ?? 'Sem título'),
                        'status'     => (string) ($item->status ?? '—'),
                        'created_at' => (string) ($item->created_at ?? now()),
                    ];
                })
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}




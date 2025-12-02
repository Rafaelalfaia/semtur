<?php

namespace App\Http\Controllers\Tecnico;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'categorias' => $this->safeCount('categorias'),
            'empresas'   => $this->safeCount('empresas'),
            'pontos'     => $this->safeCount('pontos_turisticos'),
        ];

        return view('console.role-dashboard', [
            'title' => 'Painel do Técnico',
            'role'  => 'tecnico',
            'stats' => $stats,
        ]);
    }

    private function safeCount(string $table): int
    {
        if (!Schema::hasTable($table)) return 0;

        try {
            return (int) DB::table($table)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}

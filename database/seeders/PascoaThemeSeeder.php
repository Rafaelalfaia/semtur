<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class PascoaThemeSeeder extends Seeder
{
    public function run(): void
    {
        Theme::query()->updateOrCreate(
            ['slug' => 'pascoa'],
            [
                'name' => 'Páscoa',
                'base_theme' => 'default',
                'type' => 'sazonal',
                'application_scopes' => [Theme::SCOPE_GLOBAL],
                'status' => Theme::STATUS_DISPONIVEL,
                'description' => 'Tema sazonal de Páscoa com paleta suave, contraste elegante e clima acolhedor para console, site e autenticação.',
                'starts_at' => '2026-03-20 00:00:00',
                'ends_at' => '2026-04-20 23:59:59',
                'is_default' => false,
                'tokens' => [
                    'ui-app-bg' => '#fff5ee',
                    'ui-app-bg-soft' => '#fffaf6',
                    'ui-frame-bg' => 'rgba(255, 247, 239, 0.92)',
                    'ui-frame-border' => 'rgba(255, 255, 255, 0.76)',
                    'ui-frame-shadow' => '0 28px 90px rgba(146, 96, 72, 0.14)',
                    'ui-shell-bg' => '#fffdf9',
                    'ui-shell-bg-2' => '#fff1e5',
                    'ui-shell-shadow' => '0 24px 72px rgba(132, 88, 68, 0.12)',
                    'ui-shell-stroke' => 'rgba(255, 255, 255, 0.62)',
                    'ui-sidebar-bg' => 'rgba(122, 74, 104, 0.97)',
                    'ui-sidebar-border' => 'rgba(255, 255, 255, 0.10)',
                    'ui-sidebar-text' => '#fff8f6',
                    'ui-sidebar-text-soft' => 'rgba(251, 231, 224, 0.76)',
                    'ui-sidebar-hover-bg' => 'rgba(255, 255, 255, 0.08)',
                    'ui-sidebar-active-bg' => 'rgba(255, 224, 198, 0.22)',
                    'ui-sidebar-active-text' => '#fffdfa',
                    'sidebar_surface' => 'rgba(122, 74, 104, 0.97)',
                    'sidebar_text' => '#fff8f6',
                    'sidebar_section_text' => 'rgba(251, 231, 224, 0.76)',
                    'sidebar_item_bg' => 'transparent',
                    'sidebar_item_text' => '#fff8f6',
                    'sidebar_item_icon' => '#f4d8cf',
                    'sidebar_item_hover_bg' => 'rgba(255, 255, 255, 0.10)',
                    'sidebar_item_hover_text' => '#fffdfa',
                    'sidebar_item_active_bg' => 'rgba(255, 224, 198, 0.22)',
                    'sidebar_item_active_text' => '#fffdfa',
                    'sidebar_item_active_icon' => '#fff4ef',
                    'ui-topbar-bg' => 'rgba(255, 248, 242, 0.78)',
                    'ui-topbar-border' => 'rgba(167, 116, 88, 0.12)',
                    'ui-surface' => 'rgba(255, 255, 255, 0.94)',
                    'ui-surface-soft' => '#fff6ef',
                    'ui-surface-muted' => '#fbe8dc',
                    'ui-hover-surface' => '#fff1e5',
                    'ui-border' => 'rgba(167, 116, 88, 0.12)',
                    'ui-border-strong' => 'rgba(138, 82, 61, 0.20)',
                    'ui-text' => '#4f3429',
                    'ui-text-soft' => '#8d6857',
                    'ui-text-faint' => '#b69586',
                    'ui-primary' => '#d97745',
                    'ui-primary-strong' => '#b85b2b',
                    'ui-primary-soft' => '#fde6d8',
                    'ui-success' => '#3f8f68',
                    'ui-success-soft' => '#dbf3e7',
                    'ui-warning' => '#c98a2e',
                    'ui-warning-soft' => '#fff0c9',
                    'ui-danger' => '#c55a58',
                    'ui-danger-soft' => '#f9dddd',
                    'ui-state-published-bg' => '#dbf3e7',
                    'ui-state-draft-bg' => '#fff0c9',
                    'ui-state-archived-bg' => '#f6e7df',
                    'ui-focus-ring' => 'rgba(217, 119, 69, 0.24)',
                    'ui-hero-start' => '#f0a36a',
                    'ui-hero-end' => '#d68bb3',
                    'ui-hero-glow' => 'rgba(231, 181, 142, 0.34)',
                    'ui-shadow-xs' => '0 10px 28px rgba(132, 88, 68, 0.08)',
                    'ui-shadow-sm' => '0 16px 36px rgba(132, 88, 68, 0.10)',
                    'ui-shadow-md' => '0 22px 56px rgba(132, 88, 68, 0.12)',
                    'ui-shadow-lg' => '0 30px 84px rgba(132, 88, 68, 0.16)',
                    'ui-radius-sm' => '14px',
                    'ui-radius-md' => '18px',
                    'ui-radius-lg' => '26px',
                    'ui-radius-xl' => '34px',
                    'ui-spacing-card' => '1rem',
                    'ui-spacing-section' => '1.1rem',
                ],
                'assets' => [
                    'logo' => '/imagens/logosemtur.png',
                    'login_background' => '/imagens/altamira.jpg',
                    'hero_image' => '/imagens/altamira.jpg',
                ],
                'config_json' => [
                    'shell' => ['variant' => 'seasonal-easter', 'density' => 'comfortable'],
                    'site' => ['variant' => 'seasonal-easter', 'hero_variant' => 'soft-seasonal'],
                    'auth' => ['variant' => 'seasonal-easter', 'layout' => 'split'],
                    'flags' => [
                        'use_gradient_hero' => true,
                        'use_glass_topbar' => true,
                        'emphasize_brand' => true,
                    ],
                    'notes' => [
                        'internal' => 'Tema sazonal de Páscoa mantendo logos e imagens oficiais. Paleta quente e pastel para campanha institucional.',
                    ],
                ],
            ]
        );
    }
}

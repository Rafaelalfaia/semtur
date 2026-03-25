<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $theme = Theme::query()->updateOrCreate(
            ['slug' => 'institucional'],
            [
                'name' => 'Institucional',
                'base_theme' => 'default',
                'type' => 'institucional',
                'application_scopes' => [Theme::SCOPE_GLOBAL],
                'status' => Theme::STATUS_DISPONIVEL,
                'description' => 'Tema institucional padrão do console SEMTUR.',
                'tokens' => [
                    'ui-primary' => '#2f7d57',
                    'ui-primary-strong' => '#1f6a46',
                    'ui-primary-soft' => '#e4f2ea',
                    'ui-hero-start' => '#3e7d60',
                    'ui-hero-end' => '#58a27a',
                ],
                'assets' => [
                    'logo' => '/imagens/logosemtur.png',
                    'login_background' => '/imagens/altamira.jpg',
                    'hero_image' => '/imagens/altamira.jpg',
                ],
                'config_json' => [
                    'shell' => ['variant' => 'institutional', 'density' => 'comfortable'],
                    'site' => ['variant' => 'institutional', 'hero_variant' => 'default'],
                    'auth' => ['variant' => 'institutional', 'layout' => 'split'],
                ],
            ]
        );

        $settings = SystemSetting::current();

        if (! $settings->active_theme_id) {
            $settings->active_theme_id = $theme->id;
            $settings->save();
        }
    }
}

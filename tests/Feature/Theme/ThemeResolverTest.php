<?php

namespace Tests\Feature\Theme;

use App\Models\SystemSetting;
use App\Models\Theme;
use App\Models\User;
use App\Services\ThemeResolver;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionsSeeder::class);
    }

    public function test_it_falls_back_to_default_theme_when_manual_active_is_expired(): void
    {
        $defaultTheme = Theme::query()->create([
            'name' => 'Default',
            'slug' => 'default',
            'base_theme' => 'default',
            'status' => Theme::STATUS_DISPONIVEL,
            'application_scopes' => [Theme::SCOPE_GLOBAL],
            'is_default' => true,
        ]);

        $expiredTheme = Theme::query()->create([
            'name' => 'Expired',
            'slug' => 'expired',
            'base_theme' => 'graphite',
            'status' => Theme::STATUS_DISPONIVEL,
            'application_scopes' => [Theme::SCOPE_GLOBAL],
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subMinute(),
        ]);

        SystemSetting::current()->update(['active_theme_id' => $expiredTheme->id]);

        $resolver = app(ThemeResolver::class);
        $resolver->forgetCache();

        $resolved = $resolver->activeTheme(Theme::SCOPE_CONSOLE);

        $this->assertNotNull($resolved);
        $this->assertTrue($resolved->is($defaultTheme));
    }

    public function test_it_respects_scope_when_resolving_active_theme(): void
    {
        $globalTheme = Theme::query()->create([
            'name' => 'Global',
            'slug' => 'global',
            'base_theme' => 'default',
            'status' => Theme::STATUS_DISPONIVEL,
            'application_scopes' => [Theme::SCOPE_GLOBAL],
            'is_default' => true,
        ]);

        $siteTheme = Theme::query()->create([
            'name' => 'Site Only',
            'slug' => 'site-only',
            'base_theme' => 'graphite',
            'status' => Theme::STATUS_DISPONIVEL,
            'application_scopes' => [Theme::SCOPE_SITE],
        ]);

        SystemSetting::current()->update(['active_theme_id' => $siteTheme->id]);

        $resolver = app(ThemeResolver::class);
        $resolver->forgetCache();

        $this->assertTrue($resolver->activeTheme(Theme::SCOPE_SITE)->is($siteTheme));
        $this->assertTrue($resolver->activeTheme(Theme::SCOPE_CONSOLE)->is($globalTheme));
    }

    public function test_admin_preview_is_session_local_and_not_available_to_other_profiles(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $coordenador = User::factory()->create();
        $coordenador->assignRole('Coordenador');

        $previewTheme = Theme::query()->create([
            'name' => 'Preview Theme',
            'slug' => 'preview-theme',
            'base_theme' => 'graphite',
            'status' => Theme::STATUS_RASCUNHO,
            'application_scopes' => [Theme::SCOPE_CONSOLE],
        ]);

        $resolver = app(ThemeResolver::class);

        $this->actingAs($admin)->withSession([
            ThemeResolver::PREVIEW_SESSION_KEY => $previewTheme->id,
        ]);

        $this->assertTrue($resolver->previewThemeFor($admin, Theme::SCOPE_CONSOLE)->is($previewTheme));
        $this->assertNull($resolver->previewThemeFor($coordenador, Theme::SCOPE_CONSOLE));
    }

    public function test_it_uses_default_asset_fallback_when_theme_asset_is_missing(): void
    {
        $theme = Theme::query()->create([
            'name' => 'No Assets',
            'slug' => 'no-assets',
            'base_theme' => 'default',
            'status' => Theme::STATUS_DISPONIVEL,
            'application_scopes' => [Theme::SCOPE_GLOBAL],
        ]);

        $resolver = app(ThemeResolver::class);
        $assets = $resolver->resolvedAssets($theme);

        $this->assertArrayHasKey('logo', $assets);
        $this->assertStringContainsString('imagens/logosemtur.png', $assets['logo']);
    }
}

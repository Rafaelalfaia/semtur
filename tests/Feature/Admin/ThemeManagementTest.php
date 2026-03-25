<?php

namespace Tests\Feature\Admin;

use App\Models\SystemSetting;
use App\Models\Theme;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
    }

    public function test_admin_can_activate_draft_theme(): void
    {
        $theme = Theme::query()->create([
            'name' => 'Draft Theme',
            'slug' => 'draft-theme',
            'base_theme' => 'default',
            'status' => Theme::STATUS_RASCUNHO,
            'application_scopes' => [Theme::SCOPE_GLOBAL],
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.temas.activate', $theme));

        $response->assertRedirect();

        $theme->refresh();

        $this->assertSame(Theme::STATUS_DISPONIVEL, $theme->status);
        $this->assertDatabaseHas('system_settings', [
            'active_theme_id' => $theme->id,
        ]);
    }

    public function test_admin_can_archive_theme_and_replace_active_theme(): void
    {
        $fallback = Theme::query()->create([
            'name' => 'Fallback',
            'slug' => 'fallback',
            'base_theme' => 'default',
            'status' => Theme::STATUS_DISPONIVEL,
            'application_scopes' => [Theme::SCOPE_GLOBAL],
            'is_default' => true,
        ]);

        $active = Theme::query()->create([
            'name' => 'Active',
            'slug' => 'active',
            'base_theme' => 'graphite',
            'status' => Theme::STATUS_DISPONIVEL,
            'application_scopes' => [Theme::SCOPE_GLOBAL],
        ]);

        SystemSetting::current()->update(['active_theme_id' => $active->id]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.temas.archive', $active));

        $response->assertRedirect();

        $active->refresh();

        $this->assertSame(Theme::STATUS_ARQUIVADO, $active->status);
        $this->assertDatabaseHas('system_settings', [
            'active_theme_id' => $fallback->id,
        ]);
    }

    public function test_store_rejects_unknown_config_json_keys(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.temas.create'))
            ->post(route('admin.temas.store'), [
                'name' => 'Invalid Theme',
                'slug' => 'invalid-theme',
                'base_theme' => 'default',
                'status' => Theme::STATUS_DISPONIVEL,
                'application_scopes' => [Theme::SCOPE_GLOBAL],
                'config_json' => json_encode([
                    'shell' => ['variant' => 'institutional'],
                    'editorial' => ['banner' => 'nao permitido'],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

        $response->assertRedirect(route('admin.temas.create'));
        $response->assertSessionHasErrors('config_json');
        $this->assertDatabaseMissing('themes', ['slug' => 'invalid-theme']);
    }
}

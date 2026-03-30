<?php

namespace Tests\Feature;

use App\Services\InstagramFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->instance(InstagramFeed::class, new class extends InstagramFeed {
            public function getProfileFeed(string $profileUrl, int $limit = 10): array
            {
                return [];
            }
        });
    }

    public function test_home_page_can_be_rendered_with_empty_data(): void
    {
        $response = $this->get(route('site.home'));

        $response->assertOk();
        $response->assertSee('Pontos turísticos');
    }

    public function test_explore_page_can_be_rendered_with_empty_data(): void
    {
        $response = $this->get(route('site.explorar'));

        $response->assertOk();
    }

    public function test_map_page_can_be_rendered_with_empty_data(): void
    {
        $response = $this->get(route('site.mapa'));

        $response->assertOk();
    }

    public function test_portal_pages_can_be_rendered_with_empty_data(): void
    {
        $this->get(route('site.agenda'))->assertOk();
        $this->get(route('site.informacoes'))->assertOk();
        $this->get(route('site.contato'))->assertOk();
    }
}

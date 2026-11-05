<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReadmeTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_readme_is_public_and_rendered_as_html(): void
    {
        $this->get('/readme')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertSee('<h1>Asset Register API</h1>', false)
            ->assertSee('<table>', false)
            ->assertSee('href="/docs"', false);
    }

    public function test_the_root_url_leads_to_the_readme(): void
    {
        $this->get('/')->assertRedirect('/readme');
    }

    public function test_markup_in_the_markdown_cannot_inject_scripts(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'readme');
        file_put_contents($path, "# Title\n\n<script>alert(1)</script>\n\n[x](javascript:alert(1))\n");
        config(['docs.readme_path' => $path]);

        $response = $this->get('/readme')->assertOk();
        unlink($path);

        $this->assertStringNotContainsString('<script>alert', $response->getContent());
        $this->assertStringNotContainsString('href="javascript:', $response->getContent());
    }

    public function test_reading_the_readme_is_not_part_of_the_api_traffic_log(): void
    {
        $this->get('/readme')->assertOk();

        $this->assertSame(0, DB::table('api_logs')->count());
    }
}

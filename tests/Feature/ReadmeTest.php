<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReadmeTest extends TestCase
{
    use RefreshDatabase;

    public function testTheReadmeIsPublicAndRenderedAsHtml(): void
    {
        $this->get('/readme')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertSee('<h1>Asset Register API</h1>', false)
            ->assertSee('<table>', false)
            ->assertSee('href="/docs"', false);
    }

    public function testTheRootUrlLeadsToTheReadme(): void
    {
        $this->get('/')->assertRedirect('/readme');
    }

    public function testMarkupInTheMarkdownCannotInjectScripts(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'readme');
        file_put_contents($path, "# Title\n\n<script>alert(1)</script>\n\n[x](javascript:alert(1))\n");
        config(['docs.readme_path' => $path]);

        $response = $this->get('/readme')->assertOk();
        unlink($path);

        $this->assertStringNotContainsString('<script>alert', $response->getContent());
        $this->assertStringNotContainsString('href="javascript:', $response->getContent());
    }

    public function testReadingTheReadmeIsNotPartOfTheApiTrafficLog(): void
    {
        $this->get('/readme')->assertOk();

        $this->assertSame(0, DB::table('api_logs')->count());
    }
}

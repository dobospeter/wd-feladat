<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocsTest extends TestCase
{
    private function basic(string $user, string $password): array
    {
        return ['Authorization' => 'Basic ' . base64_encode("{$user}:{$password}")];
    }

    public function test_the_documentation_requires_credentials(): void
    {
        $this->get('/docs')
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate');

        $this->get('/docs/openapi.yaml')->assertUnauthorized();
    }

    public function test_wrong_credentials_are_rejected(): void
    {
        $this->withHeaders($this->basic('docs-user', 'wrong'))->get('/docs')->assertUnauthorized();
        $this->withHeaders($this->basic('wrong', 'docs-pass'))->get('/docs')->assertUnauthorized();
        $this->withHeaders($this->basic('', ''))->get('/docs')->assertUnauthorized();
    }

    public function test_the_interactive_documentation_is_served_with_valid_credentials(): void
    {
        $this->withHeaders($this->basic('docs-user', 'docs-pass'))
            ->get('/docs')
            ->assertOk()
            ->assertSee('swagger-ui', false)
            ->assertSee('/docs/openapi.yaml', false);
    }

    public function test_the_openapi_document_is_served_with_valid_credentials(): void
    {
        $response = $this->withHeaders($this->basic('docs-user', 'docs-pass'))->get('/docs/openapi.yaml');

        $response->assertOk();
        $this->assertStringContainsString('application/yaml', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('openapi: 3.0.3', $response->getFile()->getContent());
    }

    public function test_the_documentation_stays_locked_when_no_credentials_are_configured(): void
    {
        config(['docs.username' => '', 'docs.password' => '']);

        $this->withHeaders($this->basic('', ''))->get('/docs')->assertUnauthorized();
        $this->get('/docs')->assertUnauthorized();
    }
}

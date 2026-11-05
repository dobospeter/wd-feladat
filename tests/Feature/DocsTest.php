<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocsTest extends TestCase
{
    private function basic(string $user, string $password): array
    {
        return ['Authorization' => 'Basic ' . base64_encode("{$user}:{$password}")];
    }

    public function testTheDocumentationRequiresCredentials(): void
    {
        $this->get('/docs')
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate');

        $this->get('/docs/openapi.yaml')->assertUnauthorized();
    }

    public function testWrongCredentialsAreRejected(): void
    {
        $this->withHeaders($this->basic('docs-user', 'wrong'))->get('/docs')->assertUnauthorized();
        $this->withHeaders($this->basic('wrong', 'docs-pass'))->get('/docs')->assertUnauthorized();
        $this->withHeaders($this->basic('', ''))->get('/docs')->assertUnauthorized();
    }

    public function testTheInteractiveDocumentationIsServedWithValidCredentials(): void
    {
        $this->withHeaders($this->basic('docs-user', 'docs-pass'))
            ->get('/docs')
            ->assertOk()
            ->assertSee('swagger-ui', false)
            ->assertSee('/docs/openapi.yaml', false);
    }

    public function testTheOpenapiDocumentIsServedWithValidCredentials(): void
    {
        $response = $this->withHeaders($this->basic('docs-user', 'docs-pass'))->get('/docs/openapi.yaml');

        $response->assertOk();
        $this->assertStringContainsString('application/yaml', (string) $response->headers->get('Content-Type'));

        $file = $response->baseResponse;
        $this->assertInstanceOf(BinaryFileResponse::class, $file);
        $this->assertStringStartsWith('openapi: 3.0.3', $file->getFile()->getContent());
    }

    public function testTheDocumentationStaysLockedWhenNoCredentialsAreConfigured(): void
    {
        config(['docs.username' => '', 'docs.password' => '']);

        $this->withHeaders($this->basic('', ''))->get('/docs')->assertUnauthorized();
        $this->get('/docs')->assertUnauthorized();
    }
}

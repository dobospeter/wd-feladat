<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use RuntimeException;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function testAnUnknownApiRouteReturnsTheErrorEnvelope(): void
    {
        $this->getJson('/api/v1/nothing-here')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'not_found')
            ->assertJsonPath('error.message', 'The requested resource was not found.');
    }

    public function testTheEnvelopeIsJsonEvenWithoutAnAcceptHeader(): void
    {
        $this->get('/api/v1/nothing-here')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('error.code', 'not_found');
    }

    public function testADisallowedMethodReturns405WithTheAllowHeader(): void
    {
        $response = $this->deleteJson('/api/v1/devices')
            ->assertStatus(405)
            ->assertJsonPath('error.code', 'method_not_allowed');

        $this->assertStringContainsString('GET', $response->headers->get('Allow'));
    }

    public function testAMalformedJsonBodyIsReportedAsAValidationError(): void
    {
        $this->call('POST', '/api/v1/devices', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], '{"name": ')
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function testUnexpectedFailuresReturnAGeneric500WithoutLeakingDetails(): void
    {
        Route::get('/api/v1/_boom', static fn () => throw new RuntimeException('database password is hunter2'));

        $response = $this->getJson('/api/v1/_boom')
            ->assertStatus(500)
            ->assertJsonPath('error.code', 'server_error')
            ->assertJsonPath('error.message', 'An unexpected error occurred.');

        $this->assertStringNotContainsString('hunter2', $response->getContent());
        $this->assertArrayNotHasKey('trace', $response->json('error'));
    }

    public function testEveryErrorCarriesTheRequestIdOfTheResponseHeader(): void
    {
        $response = $this->getJson('/api/v1/devices/0199c0de-0000-7000-8000-000000000000')->assertNotFound();

        $this->assertNotEmpty($response->headers->get('X-Request-Id'));
        $response->assertJsonPath('error.request_id', $response->headers->get('X-Request-Id'));
    }

    public function testAClientSuppliedRequestIdIsHonoured(): void
    {
        $this->withHeader('X-Request-Id', 'client-trace-12345')
            ->getJson('/api/v1/devices')
            ->assertOk()
            ->assertHeader('X-Request-Id', 'client-trace-12345');
    }

    public function testAnUnsafeRequestIdIsReplaced(): void
    {
        $response = $this->withHeader('X-Request-Id', "bad id with spaces\t")->getJson('/api/v1/devices');

        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $response->headers->get('X-Request-Id'));
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unknown_api_route_returns_the_error_envelope(): void
    {
        $this->getJson('/api/v1/nothing-here')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'not_found')
            ->assertJsonPath('error.message', 'The requested resource was not found.');
    }

    public function test_the_envelope_is_json_even_without_an_accept_header(): void
    {
        $this->get('/api/v1/nothing-here')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('error.code', 'not_found');
    }

    public function test_a_disallowed_method_returns_405_with_the_allow_header(): void
    {
        $response = $this->deleteJson('/api/v1/devices')
            ->assertStatus(405)
            ->assertJsonPath('error.code', 'method_not_allowed');

        $this->assertStringContainsString('GET', $response->headers->get('Allow'));
    }

    public function test_a_malformed_json_body_is_reported_as_a_validation_error(): void
    {
        $this->call('POST', '/api/v1/devices', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], '{"name": ')
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_unexpected_failures_return_a_generic_500_without_leaking_details(): void
    {
        Route::get('/api/v1/_boom', static fn () => throw new RuntimeException('database password is hunter2'));

        $response = $this->getJson('/api/v1/_boom')
            ->assertStatus(500)
            ->assertJsonPath('error.code', 'server_error')
            ->assertJsonPath('error.message', 'An unexpected error occurred.');

        $this->assertStringNotContainsString('hunter2', $response->getContent());
        $this->assertArrayNotHasKey('trace', $response->json('error'));
    }

    public function test_every_error_carries_the_request_id_of_the_response_header(): void
    {
        $response = $this->getJson('/api/v1/devices/0199c0de-0000-7000-8000-000000000000')->assertNotFound();

        $this->assertNotEmpty($response->headers->get('X-Request-Id'));
        $response->assertJsonPath('error.request_id', $response->headers->get('X-Request-Id'));
    }

    public function test_a_client_supplied_request_id_is_honoured(): void
    {
        $this->withHeader('X-Request-Id', 'client-trace-12345')
            ->getJson('/api/v1/devices')
            ->assertOk()
            ->assertHeader('X-Request-Id', 'client-trace-12345');
    }

    public function test_an_unsafe_request_id_is_replaced(): void
    {
        $response = $this->withHeader('X-Request-Id', "bad id with spaces\t")->getJson('/api/v1/devices');

        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $response->headers->get('X-Request-Id'));
    }
}

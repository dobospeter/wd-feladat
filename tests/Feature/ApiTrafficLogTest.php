<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inventory\ApiLog\Domain\ApiLogEntry;
use Inventory\ApiLog\Domain\ApiLogRepository;
use RuntimeException;
use Tests\Feature\Concerns\CreatesFixtures;
use Tests\TestCase;

class ApiTrafficLogTest extends TestCase
{
    use CreatesFixtures;
    use RefreshDatabase;

    public function test_a_request_and_its_response_are_stored(): void
    {
        $response = $this->withHeaders(['User-Agent' => 'phpunit-client'])
            ->postJson('/api/v1/devices?source=test', [
                'name' => 'Latitude', 'manufacturer' => 'Dell', 'model' => '5440',
            ])->assertCreated();

        $this->assertDatabaseCount('api_logs', 1);
        $log = DB::table('api_logs')->first();

        $this->assertSame('POST', $log->method);
        $this->assertSame('/api/v1/devices', $log->path);
        $this->assertStringContainsString('/api/v1/devices?source=test', $log->uri);
        $this->assertSame('phpunit-client', $log->user_agent);
        $this->assertSame(201, $log->response_status);
        $this->assertSame($response->headers->get('X-Request-Id'), $log->request_id);
        $this->assertGreaterThanOrEqual(0, $log->duration_ms);
        $this->assertNotNull($log->created_at);

        $this->assertSame('Latitude', json_decode($log->request_body, true)['name']);
        $this->assertSame($response->json('data.id'), json_decode($log->response_body, true)['data']['id']);
        $this->assertArrayHasKey('content-type', json_decode($log->request_headers, true));
        $this->assertArrayHasKey('content-type', json_decode($log->response_headers, true));
    }

    public function test_error_responses_are_logged_too(): void
    {
        $this->getJson('/api/v1/devices/0199c0de-0000-7000-8000-000000000000');
        $this->getJson('/api/v1/does-not-exist');
        $this->postJson('/api/v1/devices', []);

        $this->assertSame([404, 404, 422], DB::table('api_logs')->orderBy('id')->pluck('response_status')->all());
        $this->assertStringContainsString('device_not_found', DB::table('api_logs')->orderBy('id')->first()->response_body);
    }

    public function test_secrets_are_redacted_before_they_are_stored(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer top-secret', 'Cookie' => 'session=abc'])
            ->postJson('/api/v1/devices', [
                'name' => 'X', 'manufacturer' => 'Y', 'model' => 'Z', 'password' => 'hunter2',
            ]);

        $log = DB::table('api_logs')->first();

        $this->assertStringNotContainsString('top-secret', $log->request_headers);
        $this->assertStringNotContainsString('session=abc', $log->request_headers);
        $this->assertStringNotContainsString('hunter2', $log->request_body);
        $this->assertStringContainsString('[REDACTED]', $log->request_body);
    }

    public function test_requests_without_a_body_store_null_bodies(): void
    {
        $this->get('/api/v1/devices', ['Accept' => 'application/json'])->assertOk();

        $log = DB::table('api_logs')->first();
        $this->assertNull($log->request_body);
        $this->assertNotNull($log->response_body);
    }

    public function test_the_documentation_is_not_part_of_the_api_traffic_log(): void
    {
        $this->get('/docs')->assertUnauthorized();
        $this->get('/up');

        $this->assertDatabaseCount('api_logs', 0);
    }

    public function test_a_failing_log_write_never_breaks_the_api_call(): void
    {
        $this->app->bind(ApiLogRepository::class, static fn () => new class () implements ApiLogRepository {
            public function save(ApiLogEntry $entry): void
            {
                throw new RuntimeException('log storage is down');
            }
        });

        $this->getJson('/api/v1/devices')->assertOk();
    }
}

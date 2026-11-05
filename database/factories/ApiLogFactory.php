<?php

declare(strict_types=1);

namespace Database\Factories;

use Override;
use App\Modules\ApiLog\Models\ApiLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApiLog>
 */
final class ApiLogFactory extends Factory
{
    protected $model = ApiLog::class;

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function definition(): array
    {
        $path = '/api/v1/' . $this->faker->randomElement(['devices', 'contracts', 'parties']);

        return [
            'request_id' => $this->faker->uuid(),
            'method' => 'GET',
            'path' => $path,
            'uri' => 'http://localhost' . $path,
            'ip' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'request_headers' => ['accept' => ['application/json']],
            'request_body' => null,
            'response_status' => 200,
            'response_headers' => ['content-type' => ['application/json']],
            'response_body' => '{"data":[]}',
            'duration_ms' => $this->faker->numberBetween(1, 250),
            'created_at' => now()->format('Y-m-d H:i:s.u'),
        ];
    }

    public function failed(): static
    {
        return $this->state([
            'method' => 'POST',
            'response_status' => $this->faker->randomElement([404, 409, 422, 500]),
            'response_body' => '{"error":{"code":"validation_failed"}}',
        ]);
    }
}

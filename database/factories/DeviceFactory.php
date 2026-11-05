<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Device\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
final class DeviceFactory extends Factory
{
    protected $model = Device::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->words(3, true)),
            'manufacturer' => $this->faker->company(),
            // The model keeps (manufacturer, model) unique.
            'model' => $this->faker->unique()->bothify('??-####-??'),
            'description' => $this->faker->optional(0.7)->sentence(),
        ];
    }
}

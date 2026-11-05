<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Party\Models\Party;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Party>
 */
final class PartyFactory extends Factory
{
    protected $model = Party::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'tax_number' => $this->faker->unique()->numerify('########-#-##'),
            'address' => $this->faker->address(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }

    public function withoutTaxNumber(): static
    {
        return $this->state(['tax_number' => null]);
    }

    public function minimal(): static
    {
        return $this->state(['tax_number' => null, 'address' => null, 'email' => null, 'phone' => null]);
    }
}

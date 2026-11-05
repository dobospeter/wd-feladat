<?php

declare(strict_types=1);

namespace Database\Factories;

use Override;
use Illuminate\Support\Carbon;
use App\Modules\Party\Models\Party;
use App\Modules\Contract\Models\Contract;
use App\Modules\Contract\Models\ContractDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
final class ContractFactory extends Factory
{
    protected $model = Contract::class;

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function definition(): array
    {
        $startsOn = Carbon::instance($this->faker->dateTimeBetween('-1 year', '+1 month'));

        return [
            'contract_number' => $this->faker->unique()->bothify('SZ-####-???-####'),
            'party_id' => Party::factory(),
            'starts_on' => $startsOn->format('Y-m-d'),
            'ends_on' => $startsOn->copy()->addMonths($this->faker->numberBetween(6, 36))->format('Y-m-d'),
            'notes' => $this->faker->optional(0.5)->sentence(),
        ];
    }

    public function openEnded(): static
    {
        return $this->state(['ends_on' => null]);
    }

    public function expired(): static
    {
        return $this->state(['starts_on' => '2024-01-01', 'ends_on' => '2024-12-31']);
    }

    /**
     * Places $count units of distinct device types on the contract, each with its own serial number.
     */
    public function withDevices(int $count = 1): static
    {
        return $this->afterCreating(static function (Contract $contract) use ($count): void {
            ContractDevice::factory()->count($count)->for($contract)->create();
        });
    }
}

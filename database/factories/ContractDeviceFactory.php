<?php

declare(strict_types=1);

namespace Database\Factories;

use Override;
use App\Modules\Device\Models\Device;
use App\Modules\Contract\Models\Contract;
use App\Modules\Contract\Models\ContractDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractDevice>
 */
final class ContractDeviceFactory extends Factory
{
    protected $model = ContractDevice::class;

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'device_id' => Device::factory(),
            // A physical unit (device + serial number) can be on one contract only.
            'serial_number' => $this->faker->unique()->bothify('SN-????-########'),
        ];
    }
}

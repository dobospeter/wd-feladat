<?php

declare(strict_types=1);

namespace App\Modules\Contract\Models;

use App\Modules\Device\Models\Device;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\ContractDeviceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;

/**
 * @property string $id
 * @property string $contract_id
 * @property string $device_id
 * @property string $serial_number
 * @property Device $device
 * @property Contract $contract
 */
#[UseFactory(ContractDeviceFactory::class)]
class ContractDevice extends Model
{
    /** @use HasFactory<ContractDeviceFactory> */
    use HasFactory;
    use HasUuids;

    public const SERIAL_NUMBER_MAX = 100;

    protected $fillable = ['contract_id', 'device_id', 'serial_number'];

    /**
     * @return BelongsTo<Contract, $this>
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * @return BelongsTo<Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}

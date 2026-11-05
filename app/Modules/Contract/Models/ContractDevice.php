<?php

declare(strict_types=1);

namespace App\Modules\Contract\Models;

use App\Modules\Device\Models\Device;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $contract_id
 * @property string $device_id
 * @property string $serial_number
 * @property Device $device
 */
class ContractDevice extends Model
{
    use HasUuids;

    public const SERIAL_NUMBER_MAX = 100;

    protected $fillable = ['contract_id', 'device_id', 'serial_number'];

    /**
     * @return BelongsTo<Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}

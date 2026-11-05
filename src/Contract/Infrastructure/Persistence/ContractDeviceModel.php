<?php

declare(strict_types=1);

namespace Inventory\Contract\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $contract_id
 * @property string $device_id
 * @property string $serial_number
 */
class ContractDeviceModel extends Model
{
    public $incrementing = false;

    protected $table = 'contract_devices';

    protected $keyType = 'string';

    protected $guarded = [];
}

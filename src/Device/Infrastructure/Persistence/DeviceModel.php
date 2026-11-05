<?php

declare(strict_types=1);

namespace Inventory\Device\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property string $manufacturer
 * @property string $model
 * @property string|null $description
 */
class DeviceModel extends Model
{
    public $incrementing = false;

    protected $table = 'devices';

    protected $keyType = 'string';

    protected $guarded = [];
}

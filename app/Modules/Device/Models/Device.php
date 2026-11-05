<?php

declare(strict_types=1);

namespace App\Modules\Device\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $name
 * @property string $manufacturer
 * @property string $model
 * @property string|null $description
 */
class Device extends Model
{
    use HasUuids;

    public const NAME_MAX = 255;
    public const MANUFACTURER_MAX = 255;
    public const MODEL_MAX = 255;
    public const DESCRIPTION_MAX = 2000;

    protected $fillable = ['name', 'manufacturer', 'model', 'description'];
}

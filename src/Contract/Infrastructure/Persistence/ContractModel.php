<?php

declare(strict_types=1);

namespace Inventory\Contract\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $contract_number
 * @property string $partner_name
 * @property string $starts_on
 * @property string|null $ends_on
 * @property string|null $notes
 * @property Collection<int, ContractDeviceModel> $devices
 */
class ContractModel extends Model
{
    // Date columns are uncast on purpose: they are plain Y-m-d strings.
    public $incrementing = false;

    protected $table = 'contracts';

    protected $keyType = 'string';

    protected $guarded = [];

    /**
     * @return HasMany<ContractDeviceModel, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(ContractDeviceModel::class, 'contract_id')->orderBy('id');
    }
}

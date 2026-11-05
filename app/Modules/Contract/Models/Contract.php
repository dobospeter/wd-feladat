<?php

declare(strict_types=1);

namespace App\Modules\Contract\Models;

use App\Modules\Party\Models\Party;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $contract_number
 * @property string $party_id
 * @property string $starts_on
 * @property string|null $ends_on
 * @property string|null $notes
 * @property Party $party
 * @property Collection<int, ContractDevice> $devices
 */
class Contract extends Model
{
    use HasUuids;

    public const NUMBER_MAX = 64;
    public const NOTES_MAX = 2000;

    // Date columns are uncast on purpose: they are plain Y-m-d strings.
    protected $fillable = ['contract_number', 'party_id', 'starts_on', 'ends_on', 'notes'];

    /**
     * @return BelongsTo<Party, $this>
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * @return HasMany<ContractDevice, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(ContractDevice::class)->orderBy('id');
    }
}

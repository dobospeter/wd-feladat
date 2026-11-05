<?php

declare(strict_types=1);

namespace App\Api\Contract\Resources;

use App\Api\Party\Resources\PartyResource;
use App\Modules\Contract\Handlers\Results\ContractResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ContractResult $resource
 */
final class ContractResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return self::contractFields($this->resource);
    }

    /**
     * @return array<string, mixed>
     */
    public static function contractFields(ContractResult $contract): array
    {
        return [
            'id' => $contract->id,
            'contract_number' => $contract->contractNumber,
            'party' => new PartyResource($contract->party),
            'starts_on' => $contract->startsOn,
            'ends_on' => $contract->endsOn,
            'notes' => $contract->notes,
        ];
    }
}

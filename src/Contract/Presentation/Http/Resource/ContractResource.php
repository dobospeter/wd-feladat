<?php

declare(strict_types=1);

namespace Inventory\Contract\Presentation\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Inventory\Contract\Domain\Contract;

/**
 * @property Contract $resource
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
    public static function contractFields(Contract $contract): array
    {
        return [
            'id' => $contract->id()->value,
            'contract_number' => $contract->contractNumber(),
            'partner_name' => $contract->partnerName(),
            'starts_on' => $contract->period()->startsOn->format('Y-m-d'),
            'ends_on' => $contract->period()->endsOn?->format('Y-m-d'),
            'notes' => $contract->notes(),
        ];
    }
}

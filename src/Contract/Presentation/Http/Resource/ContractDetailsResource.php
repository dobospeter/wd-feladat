<?php

declare(strict_types=1);

namespace Inventory\Contract\Presentation\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Inventory\Contract\Application\Data\ContractDetails;

/**
 * @property ContractDetails $resource
 */
final class ContractDetailsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ContractResource::contractFields($this->resource->contract) + [
            'devices' => AssignedDeviceResource::collection($this->resource->devices),
        ];
    }
}

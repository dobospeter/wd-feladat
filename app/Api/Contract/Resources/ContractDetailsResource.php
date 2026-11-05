<?php

declare(strict_types=1);

namespace App\Api\Contract\Resources;

use Override;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Contract\Handlers\Results\ContractDetailsResult;

/**
 * @property ContractDetailsResult $resource
 */
final class ContractDetailsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return ContractResource::contractFields($this->resource->contract) + [
            'devices' => ContractDeviceResource::collection($this->resource->devices),
        ];
    }
}

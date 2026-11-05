<?php

declare(strict_types=1);

namespace App\Api\Contract\Resources;

use Override;
use Illuminate\Http\Request;
use App\Api\Device\Resources\DeviceResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Contract\Handlers\Results\ContractDeviceResult;

/**
 * @property ContractDeviceResult $resource
 */
final class ContractDeviceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'assignment_id' => $this->resource->assignmentId,
            'serial_number' => $this->resource->serialNumber,
            'device' => new DeviceResource($this->resource->device),
        ];
    }
}

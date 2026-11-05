<?php

declare(strict_types=1);

namespace App\Api\Contract\Resources;

use App\Api\Device\Resources\DeviceResource;
use App\Modules\Contract\Handlers\Results\ContractDeviceResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ContractDeviceResult $resource
 */
final class ContractDeviceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'assignment_id' => $this->resource->assignmentId,
            'serial_number' => $this->resource->serialNumber,
            'device' => new DeviceResource($this->resource->device),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Inventory\Contract\Presentation\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Inventory\Contract\Application\Data\AssignedDevice;
use Inventory\Device\Presentation\Http\Resource\DeviceResource;

/**
 * @property AssignedDevice $resource
 */
final class AssignedDeviceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'assignment_id' => $this->resource->assignmentId->value,
            'serial_number' => $this->resource->serialNumber->value,
            'device' => new DeviceResource($this->resource->device),
        ];
    }
}

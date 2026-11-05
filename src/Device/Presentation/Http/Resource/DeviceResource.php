<?php

declare(strict_types=1);

namespace Inventory\Device\Presentation\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Inventory\Device\Domain\Device;

/**
 * @property Device $resource
 */
final class DeviceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id()->value,
            'name' => $this->resource->name(),
            'manufacturer' => $this->resource->manufacturer(),
            'model' => $this->resource->model(),
            'description' => $this->resource->description(),
        ];
    }
}

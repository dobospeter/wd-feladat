<?php

declare(strict_types=1);

namespace App\Api\Device\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Device\Handlers\Results\DeviceResult;

/**
 * @property DeviceResult $resource
 */
final class DeviceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'manufacturer' => $this->resource->manufacturer,
            'model' => $this->resource->model,
            'description' => $this->resource->description,
        ];
    }
}

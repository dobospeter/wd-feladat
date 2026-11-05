<?php

declare(strict_types=1);

namespace App\Api\Party\Resources;

use App\Modules\Party\Handlers\Results\PartyResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property PartyResult $resource
 */
final class PartyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'tax_number' => $this->resource->taxNumber,
            'address' => $this->resource->address,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
        ];
    }
}

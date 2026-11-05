<?php

declare(strict_types=1);

namespace App\Api\Device\Requests;

use App\Modules\Device\Models\Device;
use App\Core\Http\Requests\ApiRequest;
use App\Modules\Device\Handlers\Payloads\DeviceCreatePayload;
use App\Modules\Device\Handlers\Payloads\DeviceUpdatePayload;

final class DeviceSaveRequest extends ApiRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:' . Device::NAME_MAX],
            'manufacturer' => ['required', 'string', 'max:' . Device::MANUFACTURER_MAX],
            'model' => ['required', 'string', 'max:' . Device::MODEL_MAX],
            'description' => ['nullable', 'string', 'max:' . Device::DESCRIPTION_MAX],
        ];
    }

    public function toCreatePayload(): DeviceCreatePayload
    {
        return new DeviceCreatePayload(
            $this->requiredString('name'),
            $this->requiredString('manufacturer'),
            $this->requiredString('model'),
            $this->nullableString('description'),
        );
    }

    public function toUpdatePayload(string $id): DeviceUpdatePayload
    {
        return new DeviceUpdatePayload(
            $id,
            $this->requiredString('name'),
            $this->requiredString('manufacturer'),
            $this->requiredString('model'),
            $this->nullableString('description'),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Api\Device\Requests;

use App\Modules\Device\Handlers\Payloads\DeviceCreatePayload;
use App\Modules\Device\Handlers\Payloads\DeviceUpdatePayload;
use App\Modules\Device\Models\Device;
use Illuminate\Foundation\Http\FormRequest;

final class DeviceSaveRequest extends FormRequest
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
            $this->validated('name'),
            $this->validated('manufacturer'),
            $this->validated('model'),
            $this->validated('description'),
        );
    }

    public function toUpdatePayload(string $id): DeviceUpdatePayload
    {
        return new DeviceUpdatePayload(
            $id,
            $this->validated('name'),
            $this->validated('manufacturer'),
            $this->validated('model'),
            $this->validated('description'),
        );
    }
}

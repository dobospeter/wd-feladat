<?php

declare(strict_types=1);

namespace Inventory\Device\Presentation\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Inventory\Device\Application\Data\DeviceData;
use Inventory\Device\Domain\Device;

final class SaveDeviceRequest extends FormRequest
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

    public function toData(): DeviceData
    {
        return new DeviceData(
            $this->validated('name'),
            $this->validated('manufacturer'),
            $this->validated('model'),
            $this->validated('description'),
        );
    }
}

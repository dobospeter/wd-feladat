<?php

declare(strict_types=1);

namespace Inventory\Contract\Presentation\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Inventory\Contract\Domain\SerialNumber;

final class AttachDeviceRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'device_id' => ['required', 'uuid'],
            'serial_number' => ['required', 'string', 'max:' . SerialNumber::MAX_LENGTH],
        ];
    }

    public function deviceId(): string
    {
        return $this->validated('device_id');
    }

    public function serialNumber(): string
    {
        return $this->validated('serial_number');
    }
}

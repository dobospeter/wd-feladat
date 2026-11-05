<?php

declare(strict_types=1);

namespace App\Api\Contract\Requests;

use App\Modules\Contract\Handlers\Payloads\ContractDeviceAttachPayload;
use App\Modules\Contract\Models\ContractDevice;
use Illuminate\Foundation\Http\FormRequest;

final class ContractDeviceAttachRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'device_id' => ['required', 'uuid'],
            'serial_number' => ['required', 'string', 'max:' . ContractDevice::SERIAL_NUMBER_MAX],
        ];
    }

    public function toPayload(string $contractId): ContractDeviceAttachPayload
    {
        return new ContractDeviceAttachPayload(
            $contractId,
            $this->validated('device_id'),
            $this->validated('serial_number'),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Api\Contract\Requests;

use App\Core\Http\Requests\ApiRequest;
use App\Modules\Contract\Models\ContractDevice;
use App\Modules\Contract\Handlers\Payloads\ContractDeviceAttachPayload;

final class ContractDeviceAttachRequest extends ApiRequest
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
            $this->requiredString('device_id'),
            $this->requiredString('serial_number'),
        );
    }
}

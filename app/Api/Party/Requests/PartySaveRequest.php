<?php

declare(strict_types=1);

namespace App\Api\Party\Requests;

use App\Modules\Party\Models\Party;
use App\Core\Http\Requests\ApiRequest;
use App\Modules\Party\Handlers\Payloads\PartyCreatePayload;
use App\Modules\Party\Handlers\Payloads\PartyUpdatePayload;

final class PartySaveRequest extends ApiRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:' . Party::NAME_MAX],
            'tax_number' => ['nullable', 'string', 'max:' . Party::TAX_NUMBER_MAX],
            'address' => ['nullable', 'string', 'max:' . Party::ADDRESS_MAX],
            'email' => ['nullable', 'email', 'max:' . Party::EMAIL_MAX],
            'phone' => ['nullable', 'string', 'max:' . Party::PHONE_MAX],
        ];
    }

    public function toCreatePayload(): PartyCreatePayload
    {
        return new PartyCreatePayload(
            $this->requiredString('name'),
            $this->nullableString('tax_number'),
            $this->nullableString('address'),
            $this->nullableString('email'),
            $this->nullableString('phone'),
        );
    }

    public function toUpdatePayload(string $id): PartyUpdatePayload
    {
        return new PartyUpdatePayload(
            $id,
            $this->requiredString('name'),
            $this->nullableString('tax_number'),
            $this->nullableString('address'),
            $this->nullableString('email'),
            $this->nullableString('phone'),
        );
    }
}

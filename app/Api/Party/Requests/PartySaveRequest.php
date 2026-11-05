<?php

declare(strict_types=1);

namespace App\Api\Party\Requests;

use App\Modules\Party\Handlers\Payloads\PartyCreatePayload;
use App\Modules\Party\Handlers\Payloads\PartyUpdatePayload;
use App\Modules\Party\Models\Party;
use Illuminate\Foundation\Http\FormRequest;

final class PartySaveRequest extends FormRequest
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
            $this->validated('name'),
            $this->validated('tax_number'),
            $this->validated('address'),
            $this->validated('email'),
            $this->validated('phone'),
        );
    }

    public function toUpdatePayload(string $id): PartyUpdatePayload
    {
        return new PartyUpdatePayload(
            $id,
            $this->validated('name'),
            $this->validated('tax_number'),
            $this->validated('address'),
            $this->validated('email'),
            $this->validated('phone'),
        );
    }
}

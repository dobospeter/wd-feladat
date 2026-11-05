<?php

declare(strict_types=1);

namespace App\Modules\Party\Handlers\Payloads;

abstract readonly class PartyPayload
{
    public function __construct(
        public string $name,
        public ?string $taxNumber,
        public ?string $address,
        public ?string $email,
        public ?string $phone,
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function attributes(): array
    {
        return [
            'name' => $this->name,
            'tax_number' => $this->taxNumber,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}

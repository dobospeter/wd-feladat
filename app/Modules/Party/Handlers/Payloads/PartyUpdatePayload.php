<?php

declare(strict_types=1);

namespace App\Modules\Party\Handlers\Payloads;

final readonly class PartyUpdatePayload extends PartyPayload
{
    public function __construct(
        public string $id,
        string $name,
        ?string $taxNumber,
        ?string $address,
        ?string $email,
        ?string $phone,
    ) {
        parent::__construct($name, $taxNumber, $address, $email, $phone);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Party\Handlers\Results;

use App\Modules\Party\Models\Party;

final readonly class PartyResult
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $taxNumber,
        public ?string $address,
        public ?string $email,
        public ?string $phone,
    ) {
    }

    public static function fromModel(Party $party): self
    {
        return new self($party->id, $party->name, $party->tax_number, $party->address, $party->email, $party->phone);
    }
}

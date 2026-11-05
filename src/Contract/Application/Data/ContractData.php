<?php

declare(strict_types=1);

namespace Inventory\Contract\Application\Data;

use DateTimeImmutable;

final readonly class ContractData
{
    public function __construct(
        public string $contractNumber,
        public string $partnerName,
        public DateTimeImmutable $startsOn,
        public ?DateTimeImmutable $endsOn,
        public ?string $notes,
    ) {
    }
}

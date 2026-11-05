<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers\Payloads;

use DateTimeImmutable;

final readonly class ContractUpdatePayload extends ContractPayload
{
    public function __construct(
        public string $id,
        string $contractNumber,
        string $partyId,
        DateTimeImmutable $startsOn,
        ?DateTimeImmutable $endsOn,
        ?string $notes,
    ) {
        parent::__construct($contractNumber, $partyId, $startsOn, $endsOn, $notes);
    }
}

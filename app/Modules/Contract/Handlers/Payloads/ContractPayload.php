<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers\Payloads;

use App\Modules\Contract\ValueObjects\ContractPeriod;
use DateTimeImmutable;

abstract readonly class ContractPayload
{
    private const DATE_FORMAT = 'Y-m-d';

    public function __construct(
        public string $contractNumber,
        public string $partyId,
        public DateTimeImmutable $startsOn,
        public ?DateTimeImmutable $endsOn,
        public ?string $notes,
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function attributes(): array
    {
        $period = new ContractPeriod($this->startsOn, $this->endsOn);

        return [
            'contract_number' => $this->contractNumber,
            'party_id' => $this->partyId,
            'starts_on' => $period->startsOn->format(self::DATE_FORMAT),
            'ends_on' => $period->endsOn?->format(self::DATE_FORMAT),
            'notes' => $this->notes,
        ];
    }
}

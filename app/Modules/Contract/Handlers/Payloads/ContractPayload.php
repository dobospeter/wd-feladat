<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers\Payloads;

use DateTimeImmutable;
use App\Modules\Contract\ValueObjects\ContractPeriod;

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
     * @return array{contract_number: string, party_id: string, starts_on: string, ends_on: string|null, notes: string|null}
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

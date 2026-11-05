<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers\Results;

use App\Modules\Contract\Models\Contract;
use App\Modules\Party\Handlers\Results\PartyResult;

final readonly class ContractResult
{
    public function __construct(
        public string $id,
        public string $contractNumber,
        public PartyResult $party,
        public string $startsOn,
        public ?string $endsOn,
        public ?string $notes,
    ) {
    }

    public static function fromModel(Contract $contract): self
    {
        return new self(
            $contract->id,
            $contract->contract_number,
            PartyResult::fromModel($contract->party),
            $contract->starts_on,
            $contract->ends_on,
            $contract->notes,
        );
    }
}

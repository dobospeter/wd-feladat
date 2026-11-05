<?php

declare(strict_types=1);

namespace Inventory\Contract\Domain;

use DateTimeImmutable;
use Inventory\Contract\Domain\Exception\InvalidContractPeriod;

final readonly class ContractPeriod
{
    public DateTimeImmutable $startsOn;

    public ?DateTimeImmutable $endsOn;

    public function __construct(DateTimeImmutable $startsOn, ?DateTimeImmutable $endsOn)
    {
        $this->startsOn = $startsOn->setTime(0, 0);
        $this->endsOn = $endsOn?->setTime(0, 0);

        if ($this->endsOn !== null && $this->endsOn < $this->startsOn) {
            throw new InvalidContractPeriod();
        }
    }
}

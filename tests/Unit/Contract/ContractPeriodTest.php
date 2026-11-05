<?php

declare(strict_types=1);

namespace Tests\Unit\Contract;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use App\Modules\Contract\ValueObjects\ContractPeriod;
use App\Modules\Contract\Exceptions\InvalidContractPeriodException;

class ContractPeriodTest extends TestCase
{
    public function testThePeriodCannotEndBeforeItStarts(): void
    {
        $this->expectException(InvalidContractPeriodException::class);

        new ContractPeriod(new DateTimeImmutable('2026-02-01'), new DateTimeImmutable('2026-01-31'));
    }

    public function testAOneDayPeriodIsValidRegardlessOfTheTimeOfDay(): void
    {
        $period = new ContractPeriod(new DateTimeImmutable('2026-02-01 10:00'), new DateTimeImmutable('2026-02-01 09:00'));

        $this->assertEquals($period->startsOn, $period->endsOn);
    }

    public function testAnOpenEndedPeriodIsValid(): void
    {
        $this->assertNull((new ContractPeriod(new DateTimeImmutable('2026-02-01'), null))->endsOn);
    }
}

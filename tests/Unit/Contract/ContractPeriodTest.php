<?php

namespace Tests\Unit\Contract;

use App\Modules\Contract\Exceptions\InvalidContractPeriodException;
use App\Modules\Contract\ValueObjects\ContractPeriod;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ContractPeriodTest extends TestCase
{
    public function test_the_period_cannot_end_before_it_starts(): void
    {
        $this->expectException(InvalidContractPeriodException::class);

        new ContractPeriod(new DateTimeImmutable('2026-02-01'), new DateTimeImmutable('2026-01-31'));
    }

    public function test_a_one_day_period_is_valid_regardless_of_the_time_of_day(): void
    {
        $period = new ContractPeriod(new DateTimeImmutable('2026-02-01 10:00'), new DateTimeImmutable('2026-02-01 09:00'));

        $this->assertEquals($period->startsOn, $period->endsOn);
    }

    public function test_an_open_ended_period_is_valid(): void
    {
        $this->assertNull((new ContractPeriod(new DateTimeImmutable('2026-02-01'), null))->endsOn);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Contract;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use DateMalformedStringException;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Modules\Contract\ValueObjects\ContractPeriod;
use App\Modules\Contract\Exceptions\InvalidContractPeriodException;

class ContractPeriodTest extends TestCase
{
    /**
     * @return array<string, array{string, ?string}>
     */
    public static function validPeriods(): array
    {
        return [
            'ends after it starts' => ['2026-01-01', '2026-12-31'],
            'ends the next day' => ['2026-01-31', '2026-02-01'],
            'one day period' => ['2026-02-01', '2026-02-01'],
            'one day period, end earlier in the day' => ['2026-02-01 10:00', '2026-02-01 09:00'],
            'open ended' => ['2026-02-01', null],
            'spans a leap day' => ['2028-02-28', '2028-03-01'],
        ];
    }

    /**
     * @return array<string, array{string, ?string}>
     */
    public static function invalidPeriods(): array
    {
        return [
            'ends the day before' => ['2026-02-01', '2026-01-31'],
            'ends a year before' => ['2026-06-01', '2025-06-01'],
            'ends before the start across a year boundary' => ['2026-01-01', '2025-12-31'],
        ];
    }

    /**
     * @throws DateMalformedStringException
     */
    #[DataProvider('validPeriods')]
    public function testValidPeriodsAreAccepted(string $startsOn, ?string $endsOn): void
    {
        // Arrange
        $start = new DateTimeImmutable($startsOn);
        $end = $endsOn === null ? null : new DateTimeImmutable($endsOn);

        // Act
        $period = new ContractPeriod($start, $end);

        // Assert
        $this->assertSame('00:00:00', $period->startsOn->format('H:i:s'));
        $this->assertSame($end === null, $period->endsOn === null);
        $this->assertSame($end === null ? null : '00:00:00', $period->endsOn?->format('H:i:s'));
    }

    /**
     * @throws DateMalformedStringException
     */
    #[DataProvider('invalidPeriods')]
    public function testInvalidPeriodsAreRejected(string $startsOn, string $endsOn): void
    {
        // Arrange
        $start = new DateTimeImmutable($startsOn);
        $end = new DateTimeImmutable($endsOn);
        $this->expectException(InvalidContractPeriodException::class);

        // Act
        new ContractPeriod($start, $end);

        // Assert: the expected exception is verified by PHPUnit.
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testAOneDayPeriodStartsAndEndsOnTheSameInstant(): void
    {
        // Arrange
        $start = new DateTimeImmutable('2026-02-01 10:00');
        $end = new DateTimeImmutable('2026-02-01 09:00');

        // Act
        $period = new ContractPeriod($start, $end);

        // Assert
        $this->assertEquals($period->startsOn, $period->endsOn);
    }
}

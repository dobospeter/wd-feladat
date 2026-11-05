<?php

declare(strict_types=1);

namespace Inventory\Contract\Presentation\Http\Request;

use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Inventory\Contract\Application\Data\ContractData;
use Inventory\Contract\Domain\Contract;

/** The ends_on >= starts_on rule is enforced by ContractPeriod, not duplicated here. */
final class SaveContractRequest extends FormRequest
{
    private const DATE_FORMAT = 'Y-m-d';

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'contract_number' => ['required', 'string', 'max:' . Contract::NUMBER_MAX],
            'partner_name' => ['required', 'string', 'max:' . Contract::PARTNER_MAX],
            'starts_on' => ['required', 'date_format:' . self::DATE_FORMAT],
            'ends_on' => ['nullable', 'date_format:' . self::DATE_FORMAT],
            'notes' => ['nullable', 'string', 'max:' . Contract::NOTES_MAX],
        ];
    }

    public function toData(): ContractData
    {
        return new ContractData(
            $this->validated('contract_number'),
            $this->validated('partner_name'),
            $this->parseDate($this->validated('starts_on')),
            $this->validated('ends_on') === null ? null : $this->parseDate($this->validated('ends_on')),
            $this->validated('notes'),
        );
    }

    private function parseDate(string $value): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat('!' . self::DATE_FORMAT, $value);
    }
}

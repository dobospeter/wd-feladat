<?php

declare(strict_types=1);

namespace App\Api\Contract\Requests;

use App\Modules\Contract\Handlers\Payloads\ContractCreatePayload;
use App\Modules\Contract\Handlers\Payloads\ContractUpdatePayload;
use App\Modules\Contract\Models\Contract;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;

/** The ends_on >= starts_on rule is enforced by ContractPeriod, not duplicated here. */
final class ContractSaveRequest extends FormRequest
{
    private const DATE_FORMAT = 'Y-m-d';

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'contract_number' => ['required', 'string', 'max:' . Contract::NUMBER_MAX],
            'party_id' => ['required', 'uuid'],
            'starts_on' => ['required', 'date_format:' . self::DATE_FORMAT],
            'ends_on' => ['nullable', 'date_format:' . self::DATE_FORMAT],
            'notes' => ['nullable', 'string', 'max:' . Contract::NOTES_MAX],
        ];
    }

    public function toCreatePayload(): ContractCreatePayload
    {
        return new ContractCreatePayload(
            $this->validated('contract_number'),
            $this->validated('party_id'),
            $this->parseDate($this->validated('starts_on')),
            $this->validated('ends_on') === null ? null : $this->parseDate($this->validated('ends_on')),
            $this->validated('notes'),
        );
    }

    public function toUpdatePayload(string $id): ContractUpdatePayload
    {
        return new ContractUpdatePayload(
            $id,
            $this->validated('contract_number'),
            $this->validated('party_id'),
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

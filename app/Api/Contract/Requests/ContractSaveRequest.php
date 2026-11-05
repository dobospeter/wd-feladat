<?php

declare(strict_types=1);

namespace App\Api\Contract\Requests;

use LogicException;
use DateTimeImmutable;
use App\Core\Http\Requests\ApiRequest;
use App\Modules\Contract\Models\Contract;
use App\Modules\Contract\Handlers\Payloads\ContractCreatePayload;
use App\Modules\Contract\Handlers\Payloads\ContractUpdatePayload;

/** The ends_on >= starts_on rule is enforced by ContractPeriod, not duplicated here. */
final class ContractSaveRequest extends ApiRequest
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
            $this->requiredString('contract_number'),
            $this->requiredString('party_id'),
            $this->startsOn(),
            $this->endsOn(),
            $this->nullableString('notes'),
        );
    }

    public function toUpdatePayload(string $id): ContractUpdatePayload
    {
        return new ContractUpdatePayload(
            $id,
            $this->requiredString('contract_number'),
            $this->requiredString('party_id'),
            $this->startsOn(),
            $this->endsOn(),
            $this->nullableString('notes'),
        );
    }

    private function startsOn(): DateTimeImmutable
    {
        return $this->parseDate($this->requiredString('starts_on'));
    }

    private function endsOn(): ?DateTimeImmutable
    {
        $value = $this->nullableString('ends_on');

        return $value === null ? null : $this->parseDate($value);
    }

    private function parseDate(string $value): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat('!' . self::DATE_FORMAT, $value)
            ?: throw new LogicException("Validated date '{$value}' cannot be parsed.");
    }
}

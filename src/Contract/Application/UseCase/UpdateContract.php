<?php

declare(strict_types=1);

namespace Inventory\Contract\Application\UseCase;

use Inventory\Contract\Application\ContractDetailsAssembler;
use Inventory\Contract\Application\Data\ContractData;
use Inventory\Contract\Application\Data\ContractDetails;
use Inventory\Contract\Domain\ContractId;
use Inventory\Contract\Domain\ContractPeriod;
use Inventory\Contract\Domain\ContractRepository;
use Inventory\Contract\Domain\Exception\ContractNumberAlreadyExists;

final readonly class UpdateContract
{
    public function __construct(
        private ContractRepository $contracts,
        private ContractDetailsAssembler $assembler,
    ) {
    }

    public function handle(ContractId $id, ContractData $data): ContractDetails
    {
        $contract = $this->contracts->get($id);

        $contract->revise(
            $data->contractNumber,
            $data->partnerName,
            new ContractPeriod($data->startsOn, $data->endsOn),
            $data->notes,
        );

        if ($this->contracts->numberExists($contract->contractNumber(), $id)) {
            throw new ContractNumberAlreadyExists($contract->contractNumber());
        }

        $this->contracts->save($contract);

        return $this->assembler->assemble($contract);
    }
}

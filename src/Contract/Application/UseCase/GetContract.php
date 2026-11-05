<?php

declare(strict_types=1);

namespace Inventory\Contract\Application\UseCase;

use Inventory\Contract\Application\ContractDetailsAssembler;
use Inventory\Contract\Application\Data\ContractDetails;
use Inventory\Contract\Domain\ContractId;
use Inventory\Contract\Domain\ContractRepository;

final readonly class GetContract
{
    public function __construct(
        private ContractRepository $contracts,
        private ContractDetailsAssembler $assembler,
    ) {
    }

    public function handle(ContractId $id): ContractDetails
    {
        return $this->assembler->assemble($this->contracts->get($id));
    }
}

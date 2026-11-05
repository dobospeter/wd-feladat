<?php

declare(strict_types=1);

namespace Inventory\Contract\Application\UseCase;

use Inventory\Contract\Domain\ContractId;
use Inventory\Contract\Domain\ContractRepository;

final readonly class DeleteContract
{
    public function __construct(private ContractRepository $contracts)
    {
    }

    public function handle(ContractId $id): void
    {
        $this->contracts->get($id);

        $this->contracts->delete($id);
    }
}

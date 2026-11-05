<?php

declare(strict_types=1);

namespace Inventory\Contract\Application\UseCase;

use Inventory\Contract\Domain\Contract;
use Inventory\Contract\Domain\ContractRepository;
use Inventory\Shared\Domain\Pagination\Page;
use Inventory\Shared\Domain\Pagination\PageRequest;

final readonly class ListContracts
{
    public function __construct(private ContractRepository $contracts)
    {
    }

    /**
     * @return Page<Contract>
     */
    public function handle(PageRequest $request): Page
    {
        return $this->contracts->paginate($request);
    }
}

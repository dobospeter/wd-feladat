<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers;

use App\Core\Pagination\Page;
use App\Modules\Contract\Handlers\Payloads\ContractListPayload;
use App\Modules\Contract\Handlers\Results\ContractResult;
use App\Modules\Contract\Models\Contract;
use App\Modules\Contract\Repositories\ContractRepository;

final readonly class ContractListHandler
{
    public function __construct(private ContractRepository $contracts)
    {
    }

    /**
     * @return Page<ContractResult>
     */
    public function handle(ContractListPayload $payload): Page
    {
        $paginator = $this->contracts->paginate($payload->pagination);

        return new Page(
            array_map(static fn (Contract $contract): ContractResult => ContractResult::fromModel($contract), $paginator->items()),
            $paginator->total(),
            $payload->pagination,
        );
    }
}

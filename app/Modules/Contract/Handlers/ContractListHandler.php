<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers;

use App\Core\Pagination\Page;
use App\Modules\Contract\Handlers\Results\ContractResult;
use App\Modules\Contract\Repositories\ContractRepository;
use App\Modules\Contract\Handlers\Payloads\ContractListPayload;

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
        return Page::fromPaginator(
            $this->contracts->paginate($payload->pagination),
            $payload->pagination,
            ContractResult::fromModel(...),
        );
    }
}

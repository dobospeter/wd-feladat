<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers\Payloads;

use App\Core\Pagination\PageRequest;

final readonly class ContractListPayload
{
    public function __construct(public PageRequest $pagination)
    {
    }
}

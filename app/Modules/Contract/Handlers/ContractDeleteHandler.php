<?php

declare(strict_types=1);

namespace App\Modules\Contract\Handlers;

use App\Modules\Contract\Repositories\ContractRepository;
use App\Modules\Contract\Handlers\Payloads\ContractDeletePayload;

final readonly class ContractDeleteHandler
{
    public function __construct(private ContractRepository $contracts)
    {
    }

    public function handle(ContractDeletePayload $payload): void
    {
        $this->contracts->delete($this->contracts->get($payload->id));
    }
}

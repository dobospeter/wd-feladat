<?php

declare(strict_types=1);

namespace Inventory\Contract\Domain;

use Inventory\Contract\Domain\Exception\ContractNotFound;
use Inventory\Device\Domain\DeviceId;
use Inventory\Shared\Domain\Pagination\Page;
use Inventory\Shared\Domain\Pagination\PageRequest;

interface ContractRepository
{
    /**
     * @throws ContractNotFound
     */
    public function get(ContractId $id): Contract;

    /**
     * @return Page<Contract>
     */
    public function paginate(PageRequest $request): Page;

    public function save(Contract $contract): void;

    public function delete(ContractId $id): void;

    public function numberExists(string $contractNumber, ?ContractId $except = null): bool;

    public function unitExists(DeviceId $deviceId, SerialNumber $serialNumber): bool;
}

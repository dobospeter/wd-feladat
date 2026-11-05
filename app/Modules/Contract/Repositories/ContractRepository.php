<?php

declare(strict_types=1);

namespace App\Modules\Contract\Repositories;

use App\Core\Pagination\PageRequest;
use App\Modules\Contract\Exceptions\ContractNotFoundException;
use App\Modules\Contract\Models\Contract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ContractRepository
{
    public function get(string $id): Contract
    {
        return Contract::query()->find($id) ?? throw new ContractNotFoundException($id);
    }

    /**
     * @return LengthAwarePaginator<int, Contract>
     */
    public function paginate(PageRequest $request): LengthAwarePaginator
    {
        return Contract::query()->with('party')->orderBy('id')->paginate(perPage: $request->perPage, page: $request->page);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Contract
    {
        return Contract::query()->create($attributes);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(Contract $contract, array $attributes): Contract
    {
        $contract->update($attributes);

        return $contract;
    }

    public function delete(Contract $contract): void
    {
        // The foreign key cascades to the devices placed on the contract.
        $contract->delete();
    }

    /**
     * Loads what ContractDetailsResult needs: the party and the placed devices with their device type.
     */
    public function loadDetails(Contract $contract): Contract
    {
        return $contract->load(['party', 'devices.device']);
    }

    public function numberExists(string $contractNumber, ?string $exceptId = null): bool
    {
        return Contract::query()
            ->where('contract_number', $contractNumber)
            ->when($exceptId !== null, static fn ($query) => $query->whereKeyNot($exceptId))
            ->exists();
    }
}

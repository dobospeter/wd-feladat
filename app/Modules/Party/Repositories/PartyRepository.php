<?php

declare(strict_types=1);

namespace App\Modules\Party\Repositories;

use App\Core\Pagination\PageRequest;
use App\Modules\Party\Exceptions\PartyInUseException;
use App\Modules\Party\Exceptions\PartyNotFoundException;
use App\Modules\Party\Models\Party;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;

final class PartyRepository
{
    public function get(string $id): Party
    {
        return Party::query()->find($id) ?? throw new PartyNotFoundException($id);
    }

    /**
     * @return LengthAwarePaginator<int, Party>
     */
    public function paginate(PageRequest $request): LengthAwarePaginator
    {
        return Party::query()
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(perPage: $request->perPage, page: $request->page);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Party
    {
        return Party::query()->create($attributes);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(Party $party, array $attributes): Party
    {
        $party->update($attributes);

        return $party;
    }

    public function delete(Party $party): void
    {
        try {
            $party->delete();
        } catch (QueryException $e) {
            // The only constraint that can reject a delete is the contracts foreign key.
            if ((string) $e->getCode() === '23000') {
                throw new PartyInUseException($party->id);
            }

            throw $e;
        }
    }

    public function taxNumberExists(string $taxNumber, ?string $exceptId = null): bool
    {
        return Party::query()
            ->where('tax_number', $taxNumber)
            ->when($exceptId !== null, static fn ($query) => $query->whereKeyNot($exceptId))
            ->exists();
    }
}

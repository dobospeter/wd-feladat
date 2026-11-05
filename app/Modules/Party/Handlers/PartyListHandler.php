<?php

declare(strict_types=1);

namespace App\Modules\Party\Handlers;

use App\Core\Pagination\Page;
use App\Modules\Party\Models\Party;
use App\Modules\Party\Handlers\Results\PartyResult;
use App\Modules\Party\Repositories\PartyRepository;
use App\Modules\Party\Handlers\Payloads\PartyListPayload;

final readonly class PartyListHandler
{
    public function __construct(private PartyRepository $parties)
    {
    }

    /**
     * @return Page<PartyResult>
     */
    public function handle(PartyListPayload $payload): Page
    {
        $paginator = $this->parties->paginate($payload->pagination);

        return new Page(
            array_values(array_map(static fn (Party $party): PartyResult => PartyResult::fromModel($party), $paginator->items())),
            $paginator->total(),
            $payload->pagination,
        );
    }
}

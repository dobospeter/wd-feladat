<?php

declare(strict_types=1);

namespace App\Core\Http\Responses;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Core\Pagination\Page;

final class PaginatedCollection
{
    private function __construct()
    {
    }

    /**
     * @param class-string<JsonResource> $resource
     */
    public static function from(Page $page, string $resource): AnonymousResourceCollection
    {
        return $resource::collection($page->items)->additional([
            'meta' => [
                'page' => $page->request->page,
                'per_page' => $page->request->perPage,
                'total' => $page->total,
                'last_page' => $page->lastPage(),
            ],
        ]);
    }
}

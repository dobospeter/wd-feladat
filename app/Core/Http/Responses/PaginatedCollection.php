<?php

declare(strict_types=1);

namespace App\Core\Http\Responses;

use App\Core\Pagination\Page;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PaginatedCollection
{
    private function __construct()
    {
    }

    /**
     * @param  Page<object>  $page
     * @param  class-string<JsonResource>  $resource
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

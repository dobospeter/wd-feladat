<?php

declare(strict_types=1);

namespace App\Api\Device\Controllers;

use App\Api\Device\Resources\DeviceResource;
use App\Core\Http\Requests\PaginationRequest;
use App\Core\Http\Responses\PaginatedCollection;
use App\Modules\Device\Handlers\DeviceListHandler;
use App\Modules\Device\Handlers\Payloads\DeviceListPayload;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class DeviceListController
{
    public function __invoke(PaginationRequest $request, DeviceListHandler $handler): AnonymousResourceCollection
    {
        return PaginatedCollection::from(
            $handler->handle(new DeviceListPayload($request->toPageRequest())),
            DeviceResource::class,
        );
    }
}

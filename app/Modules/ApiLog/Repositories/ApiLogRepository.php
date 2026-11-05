<?php

declare(strict_types=1);

namespace App\Modules\ApiLog\Repositories;

use App\Modules\ApiLog\Models\ApiLog;

final class ApiLogRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ApiLog
    {
        return ApiLog::query()->create($attributes);
    }
}

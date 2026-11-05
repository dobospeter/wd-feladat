<?php

declare(strict_types=1);

namespace Inventory\ApiLog\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

class ApiLogModel extends Model
{
    public $timestamps = false;

    protected $table = 'api_logs';

    protected $guarded = [];

    protected $casts = [
        'request_headers' => 'array',
        'response_headers' => 'array',
    ];
}

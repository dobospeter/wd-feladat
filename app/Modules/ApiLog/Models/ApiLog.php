<?php

declare(strict_types=1);

namespace App\Modules\ApiLog\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'request_headers' => 'array',
        'response_headers' => 'array',
    ];
}

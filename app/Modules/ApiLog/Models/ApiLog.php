<?php

declare(strict_types=1);

namespace App\Modules\ApiLog\Models;

use Database\Factories\ApiLogFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;

#[UseFactory(ApiLogFactory::class)]
class ApiLog extends Model
{
    /** @use HasFactory<ApiLogFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'request_headers' => 'array',
        'response_headers' => 'array',
    ];
}

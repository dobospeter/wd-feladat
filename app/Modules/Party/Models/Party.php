<?php

declare(strict_types=1);

namespace App\Modules\Party\Models;

use Database\Factories\PartyFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;

/**
 * @property string $id
 * @property string $name
 * @property string|null $tax_number
 * @property string|null $address
 * @property string|null $email
 * @property string|null $phone
 */
#[UseFactory(PartyFactory::class)]
class Party extends Model
{
    /** @use HasFactory<PartyFactory> */
    use HasFactory;
    use HasUuids;

    public const NAME_MAX = 255;
    public const TAX_NUMBER_MAX = 32;
    public const ADDRESS_MAX = 500;
    public const EMAIL_MAX = 255;
    public const PHONE_MAX = 50;

    protected $fillable = ['name', 'tax_number', 'address', 'email', 'phone'];
}

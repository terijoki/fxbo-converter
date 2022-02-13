<?php

namespace App\Validations\DTOs;

use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Casters\ArrayCaster;
use Spatie\DataTransferObject\DataTransferObject;

class RatesDto extends DataTransferObject
{
    #[CastWith(ArrayCaster::class, itemType: RateDto::class)]
    public array $rates;
}

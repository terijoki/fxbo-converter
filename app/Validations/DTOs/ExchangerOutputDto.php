<?php

namespace App\Validations\DTOs;

use Spatie\DataTransferObject\DataTransferObject;

class ExchangerOutputDto extends DataTransferObject
{
    public float  $amount;
    public string $currency;
}

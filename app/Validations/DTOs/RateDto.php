<?php

namespace App\Validations\DTOs;

use Spatie\DataTransferObject\DataTransferObject;

class RateDto extends DataTransferObject
{
    public string $currencyFrom;
    public string $currencyTo;
    public float  $amountTo;
}

<?php

namespace App\Validations\DTOs;

use Spatie\DataTransferObject\DataTransferObject;

class ExchangerInputDto extends DataTransferObject
{
    public string $amount;
    public string $from;
    public string $to;
}

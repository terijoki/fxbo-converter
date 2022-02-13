<?php

namespace App\Contracts;

interface ExchangerInterface
{
    public function getServiceRates(): array;
}

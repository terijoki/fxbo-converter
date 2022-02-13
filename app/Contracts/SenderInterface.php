<?php

namespace App\Contracts;

interface SenderInterface
{
    public function send(
        string $method,
        string $route,
        array  $data = null,
        array  $headers = null
    ): self;
}

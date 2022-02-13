<?php

return [
    'cache' => env('CACHE_RATES', true),
    'base_currency' => 'EUR',
    'services' => [
        'ecb' => [
            'class'    => \App\Services\External\EcbService::class,
            'base_uri' => 'https://www.ecb.europa.eu/',
            'type'     => 'xml'
        ],
        'coindesk' => [
            'class'    => \App\Services\External\CoindeskService::class,
            'base_uri' => 'http://api.coindesk.com/',
            'type'     => 'xml'
        ],
        //todo write new services here
    ]
];

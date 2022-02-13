<?php

namespace App\Services;

use App\Contracts\ExchangerInterface;
use App\Validations\ExchangerValidator;
use Illuminate\Support\Facades\Log;

class EcbService implements ExchangerInterface
{
    public ExchangerValidator $validator;
    public GuzzleService      $sender;

    /**
     * EcbService constructor.
     * @param ExchangerValidator $validator
     */
    public function __construct(ExchangerValidator $validator)
    {
        $this->validator = $validator;
        $this->sender = new GuzzleService(
            config('exchanger.services.ecb.base_uri')
        );
    }

    public function getServiceRates(): array
    {
        $sender = $this->sender->send(
            'GET',
            '/stats/eurofxref/eurofxref-daily.xml'
        );
        $data = $sender->toArray();
        if (!isset($data['Cube'])) {
            //можно добавить отправку данных в sentry или kibana
            Log::critical(__('not_found_ecb'));
        }
        $rates = [];
        foreach ($data['Cube']->Cube->Cube as $item) {
            if (!isset($data['Cube']->Cube->Cube['currency'])
            || !isset($data['Cube']->Cube->Cube['rate'])
            ) {
                //можно добавить отправку данных в sentry или kibana
                Log::critical(__('not_found_ecb'));
            }
            $currency = (string)$item['currency'];
            $rate     = (float)$item['rate'];

            $rates[] = $this->validator->validateRate([
                'currencyFrom' => 'EUR',
                'currencyTo'   => $currency,
                'amountTo'     => $rate,
            ]);
        }

        return $rates;
    }
}

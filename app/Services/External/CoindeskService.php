<?php

namespace App\Services\External;

use App\Contracts\ExchangerInterface;
use App\Services\GuzzleService;
use App\Validations\ExchangerValidator;
use Illuminate\Support\Facades\Log;

class CoindeskService implements ExchangerInterface
{
    public ExchangerValidator $validator;
    public GuzzleService      $sender;

    /**
     * CoindeskService constructor.
     * @param ExchangerValidator $validator
     */
    public function __construct(ExchangerValidator $validator)
    {
        $this->validator = $validator;
        $this->sender = new GuzzleService(
            config('exchanger.services.coindesk.base_uri')
        );
    }

    public function getServiceRates(): array
    {
        $sender = $this->sender->send(
            'GET',
            '/v1/bpi/historical/close.json'
        );

        $data = $sender->toArray();
        if (!isset($data['bpi']) || count($data['bpi']) === 0) {
            //можно добавить отправку данных в sentry или kibana
            Log::critical(__('not_found_coindesk'));
        }

        $value = array_pop($data['bpi']);
        $rate = $this->validator->validateRate([
            'currencyFrom' => 'BTC',
            'currencyTo'   => 'USD',
            'amountTo'     => $value,
        ]);

        return [$rate];
    }
}

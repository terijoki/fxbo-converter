<?php

namespace App\Services;

use App\Contracts\ExchangerInterface;
use App\Exceptions\CurrencyConverterException;
use App\Validations\ExchangerValidator;
use Illuminate\Support\Facades\Redis;

class RatesService
{
    public function getRates(): array
    {
        $cachedRates = $this->getCachedRates();
        if (count($cachedRates) > 0) {
            return $cachedRates;
        }

        $serviceRates = [];
        $services = config('exchanger.services');
        foreach ($services as $serviceParams) {
            $service = $this->getExchangerService($serviceParams);
            $serviceRates[] = $service->getServiceRates();

        }
        $allRates = call_user_func_array('array_merge', $serviceRates);

        if (config('exchanger.cache')) {
            $this->cacheRates($allRates);
        }

        return $allRates;
    }

    private function cacheRates(array $rates)
    {
        Redis::set('rates', json_encode($rates));
    }

    private function getCachedRates(): array
    {
        $cachedData = Redis::get('rates');

        $data = json_decode($cachedData, true);

        if ($data !== null) {
            return $data;
        }

        return [];
    }

    private function getExchangerService(array $params): ExchangerInterface
    {
        if (!class_exists($params['class'])) {
            throw new CurrencyConverterException(__('exchanger.not_found'));
        }

        $exchanger = new $params['class'](new ExchangerValidator);

        if (!$exchanger instanceof ExchangerInterface) {
            throw new CurrencyConverterException(__('exchanger.not_implement'));
        }

        return $exchanger;
    }

    private function calcAmount(
        float $amount,
        float $from,
        float $to
    ): float {
        return ($amount * $from) / ($amount * $to);
    }
}

<?php

namespace App\Services;

use App\Exceptions\CurrencyConverterException;
use App\Validations\DTOs\ExchangerInputDto;
use Illuminate\Support\Collection;

class ExchangerService
{
    private Collection $collection;

    public function calcData(ExchangerInputDto $inputData, array $rates): float
    {
        if ($inputData->from === $inputData->to) {
            return  $this->directCalc($inputData->amount, 1);
        }
        $this->collection = collect($rates);
        $this->checkDirectConvertToBaseCurrency($inputData->from);
        $this->checkDirectConvertToBaseCurrency($inputData->to);
        $data = $this->searchDirectCurrencyData(
            $inputData->from,
            $inputData->to,
        )->first();
        if ($data !== null) {
            return $inputData->from === $data['currencyFrom']
                ? $this->directCalc($inputData->amount, $data['amountTo'])
                : $this->inverseCalc($inputData->amount, $data['amountTo']);
        }

        $from = $this->searchCurrencyItem($inputData->from);
        $to   = $this->searchCurrencyItem($inputData->to);
        if ($from === null || $to === null) {
            throw new CurrencyConverterException(__('exchanger.not_found_rates'));
        }
        $ratio = $to['amountTo'] / $from['amountTo'];

        return $this->directCalc($inputData->amount, $ratio);
    }

    private function checkInBaseCurrency($field, $baseCurrency): Collection
    {
        return $this->collection->filter(function (array $item) use ($field, $baseCurrency) {
            return $item['currencyFrom'] === $baseCurrency && $item['currencyTo'] === $field;
        });
    }

    private function checkInAnyCurrency($field): Collection
    {
        return $this->collection->filter(function (array $item) use ($field) {
            return $item['currencyFrom'] === $field;
        });
    }

    private function addRateInBaseCurrency(
        string $baseCurrency,
        array $from,
        array $to
    ): void
    {
        $this->collection->push([
            "currencyFrom" => $baseCurrency,
            "currencyTo" => $from['currencyFrom'],
            "amountTo" => $this->inverseCalc(1, $from['amountTo'] / $to['amountTo'])
        ]);
    }

    private function checkDirectConvertToBaseCurrency(string $field): void
    {
        $baseCurrency = config('exchanger.base_currency');

        if ($field === $baseCurrency) return;
        //проверяем есть ли курс в евро
        $inBaseCur = $this->checkInBaseCurrency($field, $baseCurrency);
        if ($inBaseCur->count() > 0) return;
        //если нет, то проверяем есть ли курсы в любой валюте по данному запрому
        $inAnyCur = $this->checkInAnyCurrency($field);
        if ($inAnyCur->count() === 0) {
            throw new CurrencyConverterException(__('exchanger.not_found_field', [
                'field' => $field
            ]));
        }
        $from = $inAnyCur->first();
        //если есть, то проверяем найденную валюту на предмет наличия базовой
        $needleCur = $this->checkInBaseCurrency($from['currencyTo'], $baseCurrency);
        if ($needleCur->count() === 0) {
            //здесь, по-хорошему, нужен рекурсивный поиск
            throw new CurrencyConverterException(__('exchanger.not_found_field', [
                'field' => $field
            ]));
        }
        $to = $needleCur->first();
        //добавляем конвертацию в общий список
        $this->addRateInBaseCurrency($baseCurrency, $from, $to);
    }

    private function searchCurrencyItem(string $field): ?array
    {
        return $this->collection->filter(function (array $item) use ($field) {
            return $item['currencyTo'] === $field;
        })->first();
    }

    private function searchDirectCurrencyData(
        string $from,
        string $to,
    ): Collection
    {
        return $this->collection->filter(function (array $item) use ($from, $to) {
            return ($item['currencyFrom'] === $from
                    && $item['currencyTo'] === $to) ||
                ($item['currencyTo'] === $from
                    && $item['currencyFrom'] === $to);
        });
    }

    private function directCalc(
        float $amount,
        float $to,
    ): float {
        return $amount * $to;
    }

    private function inverseCalc(
        float $amount,
        float $to,
    ): float {
        return $amount / $to;
    }
}

<?php

namespace App\Validations;

use App\Rules\CurrencyRule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ExchangerValidator
{
    public function validateRate(array $args): array
    {
        $validator = Validator::make($args, $this->getRules());

        try {
            return $validator->validated();
        } catch (ValidationException $exception) {
            //можно добавить отправку данных в sentry или kibana
            Log::critical(__('invalid_format_field_for_rate'));
        }
    }

    private function getRules()
    {
        return [
            'currencyFrom' => ['required', 'string', 'min:3', 'max:3', new CurrencyRule],
            'currencyTo'   => ['required', 'string', new CurrencyRule],
            'amountTo'     => ['required', 'numeric', 'min:0', 'not_in:0'],
        ];
    }
}

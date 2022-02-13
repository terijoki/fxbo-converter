<?php

namespace App\Validations;

use App\Exceptions\CurrencyConverterException;
use App\Rules\CurrencyRule;
use App\Validations\DTOs\ExchangerInputDto;
use App\Validations\DTOs\ExchangerOutputDto;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class ServiceValidator
 */
class ServiceValidator
{
    public function getInputDto(array $args): ExchangerInputDto
    {
        $validated = $this->validateData($args, $this->getInputRules());

        return new ExchangerInputDto($validated);
    }

    public function validateData(array $args, array $rules): array
    {
        $validator = Validator::make($args, $rules);

        try {
            return $validator->validated();
        } catch (ValidationException $exception) {
            throw new CurrencyConverterException($exception->getMessage());
        }
    }

    private function getInputRules()
    {
        return [
            'amount' => ['required', 'numeric', 'min:0', 'not_in:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'from'   => ['required', 'string', 'min:3', 'max:3', new CurrencyRule],
            'to'     => ['required', 'string', new CurrencyRule],
        ];
    }

    private function getOutputRules(): array
    {
        return [
            'amount'   => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', new CurrencyRule],
        ];
    }

    public function getOutputDto(float $value, string $currency): ExchangerOutputDto
    {
        $validated = $this->validateData([
            'amount'   => round($value, 4),
            'currency' => $currency,
        ], $this->getOutputRules());

        return new ExchangerOutputDto($validated);
    }
}

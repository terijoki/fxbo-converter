<?php

namespace App\Console\Commands;

use App\Services\ExchangerService;
use App\Services\RatesService;
use App\Validations\ServiceValidator;
use Illuminate\Console\Command;

class CurrencyConverter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:convert {amount} {from} {to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert existing currency to specified currency';

    private ServiceValidator $validator;
    private RatesService $ratesService;
    private ExchangerService $exchanger;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        ServiceValidator $validator,
        RatesService $ratesService,
        ExchangerService $exchanger
    ){
        $this->validator = $validator;
        $this->ratesService = $ratesService;
        $this->exchanger = $exchanger;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inputDto = $this->validator->getInputDto($this->arguments());
        $rates = $this->ratesService->getRates();
        $value = $this->exchanger->calcData($inputDto, $rates);
        $outputDto = $this->validator->getOutputDto($value, $inputDto->to);

        $this->info(sprintf(
            "%.2f %s = %.4f %s",
            $inputDto->amount,
            $inputDto->from,
            $outputDto->amount,
            $outputDto->currency
        ));
    }
}

<?php

namespace App\Console\Commands;

use App\Services\RatesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class GetRatesToCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rates:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get uncached rates from our sources and cache it';

    private RatesService $ratesService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(RatesService $ratesService)
    {
        $this->ratesService = $ratesService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Redis::del(Redis::keys('rates'));

        $this->ratesService->getRates();

        $this->info('Rates have been successfully received from source');
    }
}

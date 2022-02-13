<?php

namespace App\Console\Commands;

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

    /**
     * Execute the console command.
     */
    public function handle()
    {

    }
}

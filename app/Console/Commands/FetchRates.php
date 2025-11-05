<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExchangeRate;
use App\Services\ExchangeRateAggregator;
use App\Services\Nbg;
use App\Services\Cba;
use App\Services\Nbk;
use App\Services\Cbar; 

class FetchRates extends Command
{
    protected $signature = 'rates:fetch';
    protected $description = 'Fetch and store USD/EUR rates from GE/AM/KZ(/AZ)';

    public function handle(): int
    {
        try {
            $providers = [
                new Nbg,
                new Cba,
                new Nbk,
                new Cbar,
            ];

            $agg   = new ExchangeRateAggregator($providers);
            $rates = $agg->collectAllRates();

            $rows = array_map(fn($d) => [
                'country'       => strtoupper($d->country),
                'currency_code' => strtoupper($d->currencyCode),
                'rate'          => $d->rate,
                'date'          => $d->date,  
                'created_at'    => now(),
                'updated_at'    => now(),
            ], $rates);

            if (!empty($rows)) {
                ExchangeRate::upsert(
                    $rows,
                    ['country','currency_code','date'],
                    ['rate','updated_at']
                );
            }

            $this->info('Stored '.count($rows).' records.');
            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('Fetch failed: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}

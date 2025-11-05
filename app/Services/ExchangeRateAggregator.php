<?php

namespace App\Services;

use App\Contracts\ExchangeRateProviderInterface;
use App\DTO\ExchangeRateDTO;

final class ExchangeRateAggregator
{
    /** @param iterable<ExchangeRateProviderInterface> $providers */
    public function __construct(private readonly iterable $providers) {}

    /** @return ExchangeRateDTO[] */
    public function collectAllRates(): array
    {
        $allRates = [];
        foreach ($this->providers as $provider) {
            foreach ($provider->getRates() as $rate) {
                if (in_array($rate->currencyCode, ['USD', 'EUR'], true)) {
                    $allRates[] = $rate;
                }
            }
        }
        return $allRates;
    }
}

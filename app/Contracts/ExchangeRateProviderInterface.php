<?php

namespace App\Contracts;

use App\DTO\ExchangeRateDTO;

interface ExchangeRateProviderInterface
{
    /** @return ExchangeRateDTO[] */
    public function getRates(): array;
}

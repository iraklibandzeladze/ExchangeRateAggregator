<?php

namespace App\DTO;

final class ExchangeRateDTO
{
    public function __construct(
        public string $country,
        public string $currencyCode,
        public float  $rate,
        public string $date
    ) {}
}

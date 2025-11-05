<?php

namespace App\Services;

use App\Contracts\ExchangeRateProviderInterface;
use App\DTO\ExchangeRateDTO;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class Nbg implements ExchangeRateProviderInterface
{
    private const URL = 'https://nbg.gov.ge/gw/api/ct/monetarypolicy/currencies/ka/json';

    public function getRates(): array
    {
        try {
            $response = Http::timeout(15)->get(self::URL);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json() ?? [];
            $latest = collect($data)->sortByDesc('date')->first();

            if (!$latest || empty($latest['currencies'])) {
                return [];
            }

            $date = Carbon::parse($latest['date'])->toDateString();
            $result = [];

            foreach ($latest['currencies'] as $row) {
                $code = strtoupper($row['code'] ?? '');
                if (!$code) continue;

                $qty  = (float)($row['quantity'] ?? 1) ?: 1.0;
                $val  = (float)($row['rate'] ?? 0);
                $rate = $qty > 0 ? $val / $qty : 0.0;

                $result[] = new ExchangeRateDTO('GE', $code, $rate, $date);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("NBG API-დან ვალუტის კურსების მიღებისას შეცდომა მოხდა.: {$e->getMessage()}");
            return [];
        }
    }
}

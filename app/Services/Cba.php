<?php

namespace App\Services;

use App\Contracts\ExchangeRateProviderInterface;
use App\DTO\ExchangeRateDTO;
use Carbon\Carbon;
use SoapClient;
use Illuminate\Support\Facades\Log;
use Exception;

class Cba implements ExchangeRateProviderInterface
{
    private const URL = 'https://api.cba.am/exchangerates.asmx?WSDL';

    public function getRates(): array
    {
        try {
            $client = new SoapClient(self::URL, ['connection_timeout' => 20]);

            $reqDate = Carbon::now('Asia/Yerevan')->toDateString();
            $res = $client->ExchangeRatesByDate(['date' => $reqDate]);
            $payload = $res->ExchangeRatesByDateResult ?? null;

            if (!$payload || !isset($payload->Rates)) {
                return [];
            }

            $date = isset($payload->CurrentDate)
                ? Carbon::parse($payload->CurrentDate)->toDateString()
                : $reqDate;

            $rows = $payload->Rates->ExchangeRate ?? [];
            if (!is_array($rows)) $rows = [$rows];

            $result = [];
            foreach ($rows as $row) {
                $code = strtoupper((string)($row->ISO ?? ''));
                if (!$code) continue;

                $amount = (float)($row->Amount ?? 1) ?: 1.0;
                $rate   = (float)($row->Rate ?? 0);
                $perOne = $amount > 0 ? $rate / $amount : 0.0;

                $result[] = new ExchangeRateDTO('AM', $code, $perOne, $date);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("Cba API-დან ვალუტის კურსების მიღებისას შეცდომა მოხდა.: {$e->getMessage()}");
            return [];
        }
    }
}

<?php

namespace App\Services;

use App\Contracts\ExchangeRateProviderInterface;
use App\DTO\ExchangeRateDTO;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class Nbk implements ExchangeRateProviderInterface
{
    private const URL = 'https://nationalbank.kz/rss/rates_all.xml';

    public function getRates(): array
    {
        try {
            $xml = Http::timeout(15)->get(self::URL)->throw()->body();
            $sx  = simplexml_load_string($xml);

            if (!$sx || !isset($sx->channel->item)) {
                return [];
            }

            $date = Carbon::now('Asia/Almaty')->toDateString();
            $result = [];

            foreach ($sx->channel->item as $v) {
                $code = strtoupper((string)$v->title);
                if (!$code) continue;

                $qty  = (float)((string)$v->quant) ?: 1.0;
                $val  = (float)((string)$v->description);
                $rate = $qty > 0 ? $val / $qty : 0.0;

                $result[] = new ExchangeRateDTO('KZ', $code, $rate, $date);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("Nbk API-დან ვალუტის კურსების მიღებისას შეცდომა მოხდა.: {$e->getMessage()}");
            return [];
        }
    }
}

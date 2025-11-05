<?php

namespace App\Services;

use App\Contracts\ExchangeRateProviderInterface;
use App\DTO\ExchangeRateDTO;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

final class Cbar implements ExchangeRateProviderInterface
{
    private const BASE = 'https://www.cbar.az/currencies';

    public function getRates(): array
    {
        try {
            $date = Carbon::now('Asia/Baku'); // ბაქოს დრო
            $xml = null;

            // სცადე დღეს და უკან 4 დღეს (სულ 5 ცდა)
            for ($i = 0; $i < 5; $i++) {
                $url = sprintf('%s/%s.xml', self::BASE, $date->format('d.m.Y'));
                $resp = Http::timeout(15)->get($url);

                if ($resp->ok()) {
                    $xml = $resp->body();
                    break;
                }

                $date = $date->subDay();
            }

            if (!$xml) {
                return [];
            }

            $sx = simplexml_load_string($xml);
            if (!$sx) {
                return [];
            }

            // <ValCurs Date="DD.MM.YYYY" ...>
            $effDate = (string)($sx['Date'] ?? '');
            $dateIso = $effDate ? Carbon::createFromFormat('d.m.Y', $effDate, 'Asia/Baku')->toDateString()
                                : Carbon::now('Asia/Baku')->toDateString();

            $out = [];
            foreach ($sx->ValType as $vt) {
                foreach ($vt->Valute as $v) {
                    $code    = strtoupper((string)$v['Code']);
                    if (!$code) continue;

                    $nominal = self::toFloat((string)$v->Nominal) ?: 1.0;
                    $value   = self::toFloat((string)$v->Value);      // AZN amount for "nominal" units
                    $perOne  = $nominal > 0 ? ($value / $nominal) : 0.0;

                    $out[] = new ExchangeRateDTO('AZ', $code, $perOne, $dateIso);
                }
            }

            return $out;
        } catch (Exception $e) {
            Log::error("Cbar API-დან ვალუტის კურსების მიღებისას შეცდომა მოხდა.: {$e->getMessage()}");
            return [];
        }
    }

    private static function toFloat(string $s): float
    {
        $s = str_replace([' ', ','], ['', '.'], trim($s));
        return is_numeric($s) ? (float)$s : 0.0;
    }
}

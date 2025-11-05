<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExchangeRate;

class ExchangeRateHistoryController extends Controller
{
    public function last7Days(Request $request)
    {
        $country = strtoupper((string)$request->query('country', '')) ?: null;

        $q = ExchangeRate::query()
            ->where('date', '>=', now()->subDays(6)->toDateString()) 
            ->orderBy('date');

        if ($country) {
            $q->where('country', $country);
        }

        $rows = $q->get(['date','country','currency_code','rate']);

        return response()->json(
            $rows->map(fn($r) => [
                'date'          => $r->date->toDateString(),
                'country'       => $r->country,
                'currency_code' => $r->currency_code,
                'rate'          => (float)$r->rate,
            ])
        );
    }
}

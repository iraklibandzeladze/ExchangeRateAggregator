<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExchangeRateHistoryController;

Route::get('/exchange-rates', [ExchangeRateHistoryController::class, 'last7Days']);

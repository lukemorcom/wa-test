<?php

use App\Http\Controllers\Api\InvestmentImportController;
use Illuminate\Support\Facades\Route;

Route::post('/investments/import', InvestmentImportController::class);

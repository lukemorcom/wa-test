<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessInvestmentImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvestmentImportController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // FormRequest if it was any% more complex than this.
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $request->file('file')->store('imports', 'local');

        ProcessInvestmentImport::dispatch($path);

        return response()->json([
            // sometimes might be helpful to return a queue_id so consumer can check status of their batch etc
            'message' => 'CSV Accepted for processing.',
        ], 202);
    }
}

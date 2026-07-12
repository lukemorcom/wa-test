<?php

namespace App\Jobs;

use App\Models\Investment;
use App\Models\Investor;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SplFileObject;

class ProcessInvestmentImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public function __construct(protected string $filePath)
    {
    }

    public function handle(): void
    {
        $disk = Storage::disk('local');
        $absolutePath = $disk->path($this->filePath);

        if (! file_exists($absolutePath)) {
            Log::error("CSV Import file not found: {$this->filePath}");

            return;
        }

        try {
            LazyCollection::make(fn () => $this->readCsv($absolutePath))
                ->filter($this->isValidRecord(...))
                ->map($this->normalizeRecord(...))
                ->filter()
                ->chunk(500)
                ->each($this->importChunk(...));

            $disk->delete($this->filePath);

            Log::info("Successfully processed and deleted CSV Import file: {$this->filePath}");
        } catch (\Throwable $e) {
            Log::error("Failed to process CSV Import file {$this->filePath}: {$e->getMessage()}", [
                'exception' => $e
            ]);
        }
    }

    protected function readCsv(string $path): \Generator
    {
        $file = new SplFileObject($path, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $headers = array_map('trim', $file->fgetcsv() ?: []);
        if (empty($headers)) {
            return;
        }

        while (! $file->eof() && ($row = $file->fgetcsv()) !== false) {
            if (count($row) === count($headers)) {
                yield array_combine($headers, array_map('trim', $row));
            }
        }
    }

    protected function isValidRecord(array $record): bool
    {
        $requiredKeys = ['investor_id', 'name', 'age', 'investment_amount', 'investment_date'];

        foreach ($requiredKeys as $key) {
            if (! isset($record[$key]) || trim($record[$key]) === '') {
                return false;
            }
        }

        return true;
    }

    protected function normalizeRecord(array $record): ?array
    {
        try {
            return [
                'investor_id' => $record['investor_id'],
                'name' => $record['name'],
                'age' => (int) $record['age'],
                'amount' => (float) $record['investment_amount'],
                'investment_date' => Carbon::parse($record['investment_date'])->format('Y-m-d'),
            ];
        } catch (\Throwable $e) {
            Log::warning("Skipped invalid CSV record for investor {$record['investor_id']}: {$e->getMessage()}");

            return null;
        }
    }

    protected function importChunk(LazyCollection $chunk): void
    {
        DB::transaction(function () use ($chunk) {
            $investors = $chunk->unique('investor_id')
                ->map(fn ($item) => [
                    'investor_id' => $item['investor_id'],
                    'name' => $item['name'],
                    'age' => $item['age'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->all();

            Investor::upsert($investors, ['investor_id'], ['name', 'age', 'updated_at']);

            $investorIdsMap = Investor::whereIn('investor_id', $chunk->pluck('investor_id')->unique())
                ->pluck('id', 'investor_id');

            $investments = $chunk->map(fn ($item) => [
                'investor_id' => $investorIdsMap[$item['investor_id']] ?? null,
                'amount' => $item['amount'],
                'investment_date' => $item['investment_date'],
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

            if (!empty($investments)) {
                Investment::upsert($investments, ['investor_id', 'investment_date'], ['amount', 'updated_at']);
            }
        });
    }
}

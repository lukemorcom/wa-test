<?php

namespace Tests\Feature;

use App\Jobs\ProcessInvestmentImport;
use App\Models\Investor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvestmentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_validates_the_uploaded_file(): void
    {
        $this->postJson('/api/investments/import')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);

        $this->postJson('/api/investments/import', [
            // invalid file
            'file' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_it_successfully_downloads_csv_and_dispatches_job(): void
    {
        Queue::fake();
        Storage::fake('local');

        $csvContent = "investor_id,name,age,investment_amount,investment_date\nINV001,John Doe,30,1000.50,2026-07-12";
        $csvFile = UploadedFile::fake()->createWithContent('investors.csv', $csvContent);

        $response = $this->postJson('/api/investments/import', [
            'file' => $csvFile,
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['message', 'file_path']);

        $filePath = $response->json('file_path');

        Storage::disk('local')->assertExists($filePath);

        Queue::assertPushed(ProcessInvestmentImport::class);
    }

    public function test_it_processes_csv_data_and_stores_in_database(): void
    {
        Storage::fake('local');

        $csvContent = <<<CSV
investor_id,name,age,investment_amount,investment_date
INV001,Terry Tibbs,30,1000.50,2026-07-12
INV002,George Agdgdwngo,25,2500.00,2026-07-12
INV001,Terry Tibbs,30,1500.75,2026-07-13
CSV;

        $filePath = 'imports/test_investors.csv';
        Storage::disk('local')->put($filePath, $csvContent);

        $job = new ProcessInvestmentImport($filePath);
        $job->handle();

        $this->assertDatabaseCount('investors', 2)
             ->assertDatabaseCount('investments', 3);

        $terry = Investor::with('investments')->where('investor_id', 'INV001')->first();
        $george = Investor::with('investments')->where('investor_id', 'INV002')->first();

        $this->assertEquals('Terry Tibbs', $terry->name);
        $this->assertCount(2, $terry->investments);
        $this->assertEquals(1000.50, $terry->investments[0]->amount);
        $this->assertEquals(1500.75, $terry->investments[1]->amount);

        $this->assertEquals('George Agdgdwngo', $george->name);
        $this->assertCount(1, $george->investments);
        $this->assertEquals(2500.00, $george->investments->first()->amount);

        Storage::disk('local')->assertMissing($filePath);
    }


    public function test_it_overwrites_amount_on_duplicate_date_per_investor(): void
    {
        Storage::fake('local');

        $csvContent = <<<CSV
investor_id,name,age,investment_amount,investment_date
INV001,John Doe,30,1000.00,2026-07-12
INV001,John Doe,30,1500.00,2026-07-12
CSV;

        $filePath = 'imports/test_investors_duplicate.csv';
        Storage::disk('local')->put($filePath, $csvContent);

        $job = new ProcessInvestmentImport($filePath);
        $job->handle();

        $this->assertDatabaseCount('investors', 1);
        $this->assertDatabaseCount('investments', 1);

        $investor = Investor::where('investor_id', 'INV001')->first();
        $this->assertDatabaseHas('investments', [
            'investor_id' => $investor->id,
            'investment_date' => '2026-07-12',
            'amount' => 1500.00,
        ]);
    }
}

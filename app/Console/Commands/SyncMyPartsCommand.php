<?php

namespace App\Console\Commands;

use App\Services\MyParts\MyPartsImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncMyPartsCommand extends Command
{
    protected $signature = 'myparts:sync {--page=1} {--per-page=50} {--file=}';

    protected $description = 'Sync product listings from MyParts into the CRM (read-only import).';

    public function handle(MyPartsImportService $importer): int
    {
        $file = $this->option('file');

        if ($file) {
            $this->info("Syncing MyParts data from local file: {$file}");

            $json = file_get_contents($file);
            $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

            $importer->syncProductsPayload($payload);

            $this->info('MyParts file sync completed.');

            return self::SUCCESS;
        }

        $page = (int) $this->option('page');
        $perPage = (int) $this->option('per-page');

        $this->info("Syncing MyParts page {$page} (per page: {$perPage})...");

        $endpoint = config('services.myparts.endpoint');

        if (! $endpoint) {
            $this->error('services.myparts.endpoint is not configured.');

            return self::FAILURE;
        }

        $response = Http::get($endpoint, [
            'page' => $page,
            'per_page' => $perPage,
        ]);

        if (! $response->ok()) {
            $this->error('Failed to fetch MyParts data: '.$response->status());

            return self::FAILURE;
        }

        $payload = $response->json();

        $importer->syncProductsPayload($payload);

        $total = $payload['data']['total'] ?? null;
        $count = is_countable($payload['data']['products'] ?? null) ? count($payload['data']['products']) : null;

        $this->info("MyParts sync completed for page {$page}. Imported {$count} products (total reported: {$total}).");

        // Pagination-ready: caller can loop over pages externally, or we can extend this command later
        // to iterate pages until all items are fetched.

        return self::SUCCESS;
    }
}

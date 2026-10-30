<?php

namespace App\Console\Commands;

use App\Services\Media\OptimizedImagePath;
use App\Services\Media\StoredImageOptimizer;
use App\Services\Media\WebpImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize
        {keys* : Object-storage keys to inspect}
        {--disk=arvan : Laravel filesystem disk}
        {--quality=82 : WebP quality from 1 to 100}
        {--min-savings=5 : Minimum percentage reduction required}
        {--upload : Upload new WebP variants; originals are never changed}
        {--report=image-pilot.csv : CSV report filename stored on the local disk}';

    protected $description = 'Safely create deterministic WebP variants without changing originals or database references';

    public function handle(): int
    {
        $quality = (int)$this->option('quality');
        $minimumSavings = (float)$this->option('min-savings');
        if ($quality < 1 || $quality > 100 || $minimumSavings < 0 || $minimumSavings > 100) {
            $this->error('Quality must be 1-100 and minimum savings must be 0-100.');
            return self::INVALID;
        }

        $diskName = (string)$this->option('disk');
        $filesystem = Storage::disk($diskName)->getDriver();
        $optimizer = new StoredImageOptimizer(
            new WebpImageOptimizer(),
            new OptimizedImagePath(),
            $quality,
            $minimumSavings,
        );
        $upload = (bool)$this->option('upload');
        $rows = [];
        $errors = 0;

        foreach (array_values(array_unique($this->argument('keys'))) as $sourceKey) {
            $sourceKey = ltrim(str_replace('\\', '/', trim((string)$sourceKey)), '/');
            try {
                $result = $optimizer->optimize($filesystem, $sourceKey, $upload);
                $rows[] = [
                    $result->status,
                    $result->sourceKey,
                    $result->targetKey,
                    $result->sourceBytes,
                    $result->targetBytes,
                    $result->savingsPercent,
                    $result->width,
                    $result->height,
                    '',
                ];
            } catch (Throwable $exception) {
                $errors++;
                $rows[] = ['error', $sourceKey, '', 0, 0, 0, 0, 0, $exception->getMessage()];
            }
        }

        $headers = [
            'status', 'source_key', 'target_key', 'source_bytes', 'target_bytes',
            'savings_percent', 'width', 'height', 'error',
        ];
        $this->table($headers, $rows);

        $reportName = basename((string)$this->option('report'));
        if ($reportName === '' || !str_ends_with(strtolower($reportName), '.csv')) {
            $reportName = 'image-pilot.csv';
        }
        Storage::disk('image_reports')->put($reportName, $this->csv($headers, $rows));
        $this->info(($upload ? 'Upload' : 'Dry-run') . " complete. Private report: storage/app/image-optimization/reports/{$reportName}");

        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function csv(array $headers, array $rows): string
    {
        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, $headers);
        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv === false ? '' : $csv;
    }
}

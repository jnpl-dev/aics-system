<?php

use Filament\Actions\Exports\Models\Export;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('exports:prune {--days=14 : Keep exports newer than this many days}', function () {
    $days = max((int) $this->option('days'), 1);
    $cutoff = now()->subDays($days);

    $query = Export::query()
        ->where(function ($builder) use ($cutoff): void {
            $builder
                ->whereNotNull('completed_at')
                ->where('completed_at', '<', $cutoff)
                ->orWhere(function ($inner) use ($cutoff): void {
                    $inner
                        ->whereNull('completed_at')
                        ->where('created_at', '<', $cutoff);
                });
        });

    $total = 0;

    $query
        ->orderBy('id')
        ->chunkById(100, function ($exports) use (&$total): void {
            foreach ($exports as $export) {
                $export->deleteFileDirectory();
                $export->delete();
                $total++;
            }
        });

    $this->info("Pruned {$total} export(s) older than {$days} day(s).");
})->purpose('Prune old Filament export records and files');

Schedule::command('exports:prune --days=14')->dailyAt('01:00');

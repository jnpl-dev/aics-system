<?php

namespace App\Filament\Resources\AuditLogs\Pages;

use App\Filament\Exports\AuditLogsExporter;
use App\Filament\Resources\AuditLogs\AuditLogResource;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make('exportAuditLogs')
                ->label('Export')
                ->exporter(AuditLogsExporter::class)
                ->formats([
                    ExportFormat::Csv,
                    ExportFormat::Xlsx,
                ]),
        ];
    }
}

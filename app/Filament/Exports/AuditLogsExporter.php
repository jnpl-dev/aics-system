<?php

namespace App\Filament\Exports;

use App\Models\AuditLog;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Str;

class AuditLogsExporter extends Exporter
{
    protected static ?string $model = AuditLog::class;

    /**
     * @return array<int, ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('log_id')->label('ID'),
            ExportColumn::make('timestamp')->label('Timestamp'),
            ExportColumn::make('module')->label('Module'),
            ExportColumn::make('action')->label('Action'),
            ExportColumn::make('description')->label('Description'),
            ExportColumn::make('user_id')->label('User ID'),
            ExportColumn::make('ip_address')->label('IP Address'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Audit log export completed. ' . number_format($export->successful_rows) . ' row(s) exported.';
    }

    /**
     * @return array<Component>
     */
    public static function getOptionsFormComponents(): array
    {
        return [
            Fieldset::make('Export Output')
                ->columns(1)
                ->schema([
                    TextInput::make('file_name')
                        ->label('File Name')
                        ->maxLength(100)
                        ->placeholder('e.g. audit-logs-april-2026')
                        ->helperText('Optional. Do not include the extension.'),
                    Select::make('storage_disk')
                        ->label('Export Storage (Server)')
                        ->options([
                            'local' => 'Local Folder',
                        ])
                        ->default('local')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Saved to local server folder.'),
                ]),
        ];
    }

    public function getFileName(Export $export): string
    {
        $customFileName = (string) ($this->getOptions()['file_name'] ?? '');
        $customFileName = trim($customFileName);

        if ($customFileName !== '') {
            return (string) Str::of($customFileName)
                ->replaceMatches('/[^A-Za-z0-9._-]/', '-')
                ->trim('-')
                ->limit(100, '');
        }

        return parent::getFileName($export);
    }

    public function getFileDisk(): string
    {
        if (array_key_exists('local', config('filesystems.disks', []))) {
            return 'local';
        }

        return parent::getFileDisk();
    }

    public function getJobConnection(): ?string
    {
        return extension_loaded('intl') ? null : 'sync';
    }
}

<?php

namespace App\Filament\Exports;

use App\Models\Application;
use App\Support\StaticUiOptionsCache;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Str;

class ApplicationsExporter extends Exporter
{
    protected static ?string $model = Application::class;

    /**
     * @return array<int, ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('application_id')->label('ID'),
            ExportColumn::make('reference_code')->label('Reference Code'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('applicant_last_name')->label('Applicant Last Name'),
            ExportColumn::make('applicant_first_name')->label('Applicant First Name'),
            ExportColumn::make('applicant_phone')->label('Applicant Phone'),
            ExportColumn::make('beneficiary_last_name')->label('Beneficiary Last Name'),
            ExportColumn::make('beneficiary_first_name')->label('Beneficiary First Name'),
            ExportColumn::make('submitted_at')->label('Submitted At'),
            ExportColumn::make('reviewed_at')->label('Reviewed At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Applications export completed. ' . number_format($export->successful_rows) . ' row(s) exported.';
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
                        ->placeholder('e.g. applications-april-2026')
                        ->helperText('Optional. Do not include the extension.'),
                    Select::make('storage_disk')
                        ->label('Export Storage (Server)')
                        ->options(StaticUiOptionsCache::exportStorageDiskOptions('admin_aics_staff'))
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

}

<?php

namespace App\Filament\Exports;

use App\Models\User;
use App\Support\StaticUiOptionsCache;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Str;

class UsersExporter extends Exporter
{
    protected static ?string $model = User::class;

    /**
     * @return array<int, ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('user_id')->label('ID'),
            ExportColumn::make('first_name')->label('First Name'),
            ExportColumn::make('last_name')->label('Last Name'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('role')->label('Role'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('created_at')->label('Created At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'User export completed. ' . number_format($export->successful_rows) . ' row(s) exported.';
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
                        ->placeholder('e.g. users-april-2026')
                        ->helperText('Optional. Do not include the extension.'),
                    Select::make('storage_disk')
                        ->label('Export Storage (Server)')
                        ->options(StaticUiOptionsCache::exportStorageDiskOptions('admin'))
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

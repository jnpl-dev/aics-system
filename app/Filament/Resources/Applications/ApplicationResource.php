<?php

namespace App\Filament\Resources\Applications;

use App\Filament\Resources\Applications\Pages\ListApplications;
use App\Filament\Resources\Applications\Pages\ReviewApplication;
use App\Filament\Resources\Applications\Pages\ViewApplication;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Applications';

    protected static UnitEnum|string|null $navigationGroup = 'Applications';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $user = auth()->user();
        $panel = Filament::getCurrentPanel();

        return $panel !== null
            && $panel->getId() === 'aics-staff'
            && $user->canAccessPanel($panel);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'review' => ReviewApplication::route('/{record}/review'),
            'view' => ViewApplication::route('/{record}/view'),
        ];
    }
}

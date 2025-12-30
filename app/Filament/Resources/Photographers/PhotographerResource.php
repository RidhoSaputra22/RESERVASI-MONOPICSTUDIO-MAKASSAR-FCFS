<?php

namespace App\Filament\Resources\Photographers;

use App\Filament\Resources\Photographers\Pages\CreatePhotographer;
use App\Filament\Resources\Photographers\Pages\EditPhotographer;
use App\Filament\Resources\Photographers\Pages\ListPhotographers;
use App\Filament\Resources\Photographers\Schemas\PhotographerForm;
use App\Filament\Resources\Photographers\Tables\PhotographersTable;
use App\Models\Photographer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PhotographerResource extends Resource
{
    protected static ?string $model = Photographer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCamera;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Fotografer';

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PhotographerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PhotographersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPhotographers::route('/'),
            'create' => CreatePhotographer::route('/create'),
            'edit' => EditPhotographer::route('/{record}/edit'),
        ];
    }
}

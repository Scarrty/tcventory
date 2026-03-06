<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\StorageLocationResource\Pages;
use App\Models\StorageLocation;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StorageLocationResource extends Resource
{
    protected static ?string $model = StorageLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('type')->required()->maxLength(40),
                TextInput::make('code')->maxLength(60)->unique(ignoreRecord: true),
                TextInput::make('description')->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('type')->searchable(),
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('description')->searchable()->limit(50),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('viewAny', StorageLocation::class) ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStorageLocations::route('/'),
            'create' => Pages\CreateStorageLocation::route('/create'),
            'edit' => Pages\EditStorageLocation::route('/{record}/edit'),
        ];
    }
}

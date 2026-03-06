<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SetResource\Pages;
use App\Models\Set;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SetResource extends Resource
{
    protected static ?string $model = Set::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('game_id')->relationship('game', 'name')->searchable()->preload()->required(),
                TextInput::make('name')->required()->maxLength(140),
                TextInput::make('code')->required()->maxLength(40),
                DatePicker::make('release_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('game.name')->label('Game')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('release_date')->date()->sortable(),
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
        return Auth::user()?->can('viewAny', Set::class) ?? false;
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('viewAny', Set::class) ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSets::route('/'),
            'create' => Pages\CreateSet::route('/create'),
            'edit' => Pages\EditSet::route('/{record}/edit'),
        ];
    }
}

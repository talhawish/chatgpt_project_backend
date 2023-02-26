<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobsResource\Pages;
use App\Filament\Resources\JobsResource\RelationManagers;
use App\Models\Jobs;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Artisan;

class JobsResource extends Resource
{
    protected static ?string $model = Jobs::class;

    protected static ?string $navigationGroup = 'jobs';

    protected static ?string $navigationIcon = 'heroicon-o-collection';


    protected static function getNavigationBadge(): ?string
	{
		return (string) Jobs::query()->count();
	}


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('queue')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('payload')
,
                Forms\Components\Toggle::make('attempts'),
                Forms\Components\DateTimePicker::make('reserved_at'),
                Forms\Components\DateTimePicker::make('available_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('queue'),
                Tables\Columns\TextColumn::make('payload')
                ->limit(20)
                ,
                Tables\Columns\IconColumn::make('attempts')
                    ->boolean(),
                Tables\Columns\TextColumn::make('reserved_at')->dateTime(),
                Tables\Columns\TextColumn::make('available_at')->dateTime(),
                Tables\Columns\TextColumn::make('created_at'),
            ])
            ->filters([
                //
            ])
            ->poll('1s')
            ->actions([
                Tables\Actions\EditAction::make(),
                
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
            'index' => Pages\ListJobs::route('/'),
            // 'create' => Pages\CreateJobs::route('/create'),
            // 'edit' => Pages\EditJobs::route('/{record}/edit'),
        ];
    }    
}

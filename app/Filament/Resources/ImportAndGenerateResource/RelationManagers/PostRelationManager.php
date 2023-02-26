<?php

namespace App\Filament\Resources\ImportAndGenerateResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostRelationManager extends RelationManager
{
    protected static string $relationship = 'post';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make()->button(),
                // Tables\Actions\DeleteAction::make(),
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('Show')->url(function ($record) {
                    return url('posts/' . $record->id.'/edit');
                })->button(),

                Tables\Actions\Action::make('Open Link')->url(function ($record) {
                    return url($record->link);
                })
                ->visible(fn($record) => !empty($record->link))
                ->color('secondary')
                ->openUrlInNewTab()
                ->button(),


            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }    
}

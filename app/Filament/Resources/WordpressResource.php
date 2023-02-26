<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WordpressResource\Pages;
use App\Filament\Resources\WordpressResource\RelationManagers;
use App\Http\Controllers\WordpressController;
use App\Models\Wordpress;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WordpressResource extends Resource
{
    protected static ?string $model = Wordpress::class;
    protected static ?string $label = "WordPress";

    protected static ?string $navigationLabel = "WordPress";

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = "Wordpress";

    protected static ?int $navigationSort = 1;


    protected static function getNavigationBadge(): ?string
	{
		return (string) Wordpress::query()->count();
	}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Add Wordpress Website')->schema([

               
                    Forms\Components\TextInput::make('website_url')
                        ->label('Website URL : ')
                        ->required(),
                 
                    Forms\Components\TextInput::make('username')
                        ->label('Username : ')
                        ->required(),
                  
                    Forms\Components\TextInput::make('password')
                        ->label('Password : ')
                        ->password()
                        ->required(),
            
                    Forms\Components\Toggle::make('status')
                        ->label('Status')
                        ->disabled()
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('website_url'),
                Tables\Columns\TextColumn::make('username'),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
            ])
            ->defaultSort('created_at', "desc")
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Connect')->action(function ($record) {
                    
                    $wp = new WordpressController($record->website_url, $record->username, $record->password);
                    
                    if($wp->connected) {
                        Wordpress::whereId($record->id)->update(['status' => $wp->connected]);
                    } else {
                        Wordpress::whereId($record->id)->update(['status' => false]);
                    }
                
                }),
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
            'index' => Pages\ListWordpresses::route('/'),
            'create' => Pages\CreateWordpress::route('/create'),
            'edit' => Pages\EditWordpress::route('/{record}/edit'),
        ];
    }    

     
}

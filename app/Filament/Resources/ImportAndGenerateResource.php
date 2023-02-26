<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Wordpress;
use App\Jobs\GeneratePost;
use Filament\Resources\Form;
use Maatwebsite\Excel\Excel;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use App\Models\ImportAndGenerate;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\ChatgptController;
// use pxlrbt\FilamentExcel\Actions\ExportAction;
use App\Http\Controllers\WordpressController;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\ImportAndGenerateResource\Pages;
use App\Filament\Resources\ImportAndGenerateResource\RelationManagers;
use App\Filament\Resources\ImportAndGenerateResource\RelationManagers\PostRelationManager;


class ImportAndGenerateResource extends Resource
{
    protected static ?string $model = ImportAndGenerate::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down';

    protected static ?string $navigationGroup = "Wordpress";
    protected static ?int $navigationSort = 4;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('website_id')
                ->label('Website')
                ->options(Wordpress::all()->pluck('website_url', 'id'))
                // ->searchable()
                ->reactive()

                ->required(),

                  Forms\Components\Select::make('categories')
                            ->required()
                            ->options(function (callable $get) {
    
                                $data = Wordpress::find($get('website_id'));
                                if ($data) {
                                    $wp = new WordpressController($data['website_url'], $data['username'], $data['password']);
                                    $result = (collect($wp->get_categories()))->pluck('name', 'id');
                                    return $result;
                                }
    
                                return [1 => 'Uncategorised'];
                            })->preload()
                            ->multiple(),

                Forms\Components\TextInput::make('keywords')
                    ->maxLength(255),
                Forms\Components\TextInput::make('matchwords')
                    ->maxLength(255),
                Forms\Components\TextInput::make('kind')
                    ->maxLength(255),
                Forms\Components\TextInput::make('subtitles')
                    ->maxLength(255),

                Forms\Components\DateTimePicker::make('scheduled_at'),

                Forms\Components\Toggle::make('is_generated')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('website.website_url')->getStateUsing(function($record) {
                    if($record->website?->website_url) {
                        return $record->website?->website_url;
                    }
                    return "";
                })->limit(15)->tooltip(fn($record) => $record->website?->website_url),
                // Tables\Columns\TextColumn::make('categories'),
                Tables\Columns\TextColumn::make('keywords')->searchable()->limit(15)->tooltip(fn($record) => $record->keywords)->sortable(),
                Tables\Columns\TextColumn::make('matchwords')->searchable()->limit(15)->tooltip(fn($record) => $record->matchwords)->sortable(),
                Tables\Columns\TextColumn::make('kind')->limit(15)->searchable()->tooltip(fn($record) => $record->kind)->sortable(),
                Tables\Columns\TextColumn::make('subtitles')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('scheduled_at')
                ->getStateUsing(function ($record) {
                
                    if(!empty($record->scheduled_at) && $record->scheduled_at > Carbon::now()) {
                        return (new Carbon($record->scheduled_at))->format('Y-m-d - H:i');
                    }

                    

                    return "";
                })
                ->sortable()->searchable()->searchable()

                ->colors([
                    'warning',
              
                ])
                ->icons([
                    'heroicon-o-pause',
                   
                ]),

                Tables\Columns\BadgeColumn::make('is_generated')->label('Has Posts')->getStateUsing(function ($record) {
                    if (count($record?->post) > 0) {
                        return count($record?->post);
                    }

                    return '';

                })->sortable(),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime(),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime(),
            ])
            ->poll('20s')
            ->defaultSort('created_at', "desc")

            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Generate')->action(function ($record) {

                    GeneratePost::dispatch($record);

                    // $gpt = new ChatgptController();

                    // $post = $gpt->generateBulgarianPost($record);

                    // dd($post);

                    Notification::make()
                        ->title('Generate Article')
                        ->body('the process has been started')
                        ->success()
                        ->send();
                })
                    ->hidden(fn($record) => empty($record->post))
                    ->button(),

              
            ])
            ->bulkActions([

                // ExportBulkAction::make('export')
                //     ->label('Export ')
                //     ->mutateFormDataUsing(function ($data) {
                //         dd($data);
                //     })

                // ->deselectRecordsAfterCompletion(),
                // Tables\Actions\BulkAction::make('export')
                //     ->icon('heroicon-o-arrow-down')
                //     ->action(function ($records) {

                      
                        
                // }),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            PostRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImportAndGenerates::route('/'),
            'create' => Pages\CreateImportAndGenerate::route('/create'),
            'edit' => Pages\EditImportAndGenerate::route('/{record}/edit'),
        ];
    }    
}

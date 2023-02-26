<?php

namespace App\Filament\Resources\PostResource\Pages;

use Closure;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\Wordpress;
use App\Jobs\GeneratePost;

use App\Imports\PostImport;
use Filament\Pages\Actions;
use GuzzleHttp\Promise\Coroutine;
use Filament\Forms\Components\Grid;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use App\Filament\Resources\PostResource;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use function GuzzleHttp\default_ca_bundle;
use App\Http\Controllers\ChatgptController;
use App\Http\Controllers\WordpressController;

use Filament\Forms\Components\DateTimePicker;
use Konnco\FilamentImport\Actions\ImportField;
use Konnco\FilamentImport\Actions\ImportAction;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('Generate')->action(function ($data) {

                $data['id'] = 0; // import_and_generate_id
    
                GeneratePost::dispatch($data);
    
                // Post::create($data);

                return true;

            })
            ->color('warning')
            ->form([
                    Select::make('website_id')->label('Website')->options(fn() => Wordpress::all()->pluck('website_url', 'id'))->reactive(),


                    Select::make('categories')
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

                    TextInput::make('keywords')
                        ->label('keywords : '),

                    TextInput::make('matchwords')
                        ->label('Matchwords : '),

                    TextInput::make('kind')
                        ->label('kind : '),
                        Grid::make()->schema([
                            TextInput::make('subtitles')
                            ->label('Subtitle  : '),
                        // DateTimePicker::make('scheduled_at'),
                        ])->columns(2),
                
                ])->color('warning'),

            Actions\CreateAction::make()->label('Create post'),

         
          
        ];
    }

   


    protected function getTableRecordClassesUsing(): ?Closure
    {

        return fn (Model $record) => match ($record->published_status) {

                1 => 'bg-gradient-to-r from-green-200 to-orange-100 dark:from-green-700 dark:to-red-700',
                // $record->published_status == 0 => 'bg-gradient-to-r from-green-200 to-orange-100',

                default => null
            };



        
        

    }
}

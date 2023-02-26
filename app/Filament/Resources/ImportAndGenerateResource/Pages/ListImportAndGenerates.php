<?php

namespace App\Filament\Resources\ImportAndGenerateResource\Pages;

use App\Models\Wordpress;
use Filament\Pages\Actions;
use App\Imports\ImportAndGenerate;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use App\Http\Controllers\WordpressController;
use App\Filament\Resources\ImportAndGenerateResource;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;

// use Konnco\FilamentImport\Actions\ImportAction;
// use Konnco\FilamentImport\ImportField;

class ListImportAndGenerates extends ListRecords
{
    protected static string $resource = ImportAndGenerateResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Create'),

            Actions\Action::make('import')
                ->label('Import File')
                ->color('danger')
                ->action(function ($data) {

                    $import = Excel::import(new ImportAndGenerate($data), $data['file'], 'public');

                    return true;
                    // dd($import);
                    })  ->form([
                Wizard::make()->schema([
                    Wizard\Step::make('First Step')->schema([
                        FileUpload::make('file')->label('Upload your File'),
                    ]),

                    Wizard\Step::make('Second Step')->schema([
                        Select::make('website_id')
                        ->label('Website')
                        ->options(Wordpress::all()->pluck('website_url', 'id'))
                        // ->searchable()
                        ->reactive(),

                        // ->required(),


                        Select::make('categories')
                        // ->required()
                        ->options(function (callable $get) {

                            $data = Wordpress::find($get('website_id'));
                            if ($data) {
                                $wp = new WordpressController($data['website_url'], $data['username'], $data['password']);
                                $result = (collect($wp->get_categories()))->pluck('name', 'id');
                                return $result;
                            }

                            return [1 => 'Uncategorised'];
                        })
                        ->multiple(),


                    ]),

                    Wizard\Step::make('Third Step')->schema([
                        Checkbox::make('start')->label('Should Start Generating Articles ?')->default(false),
                        DateTimePicker::make('scheduled_at')->label('Schedule All Articles to be generated at : ')
                    ])->columns(2),
                ]),
                ]),

                // ImportAction::make()
                // ->fields([
                //     ImportField::make('project')
                //         ->label('Project')
                //         ->helperText('Define as project helper'),
                //     ImportField::make('manager')
                //         ->label('Manager'),
                // ])
        ];
    }
}

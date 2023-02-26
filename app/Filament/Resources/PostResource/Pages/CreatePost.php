<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Http\Controllers\ChatgptController;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        
        unset($data['website']);
        // PostWordpress::create([
        //     '' => $data['website'],
        //     '' => 
        // ])
        return $data;
    }

    protected function getActions(): array
    {
        return [

            Actions\Action::make('Generate')->action(function () {
                $gpt = new ChatgptController();
                
                dd('test');

                $gpt->generatePost("hello");
            })
        ];
    }

}

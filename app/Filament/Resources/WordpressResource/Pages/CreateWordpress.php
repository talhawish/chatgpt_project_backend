<?php

namespace App\Filament\Resources\WordpressResource\Pages;

use App\Filament\Resources\WordpressResource;
use App\Models\PostWordpress;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

use Illuminate\Support\Facades\Crypt;


class CreateWordpress extends CreateRecord
{
    protected static string $resource = WordpressResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // $data['password'] = Crypt::encryptString($data['password']);

        // PostWordpress::create([
        //     '' => $data['website'],
        //     '' => 
        // ])
        return $data;
    }

}

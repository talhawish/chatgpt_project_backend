<?php

namespace App\Filament\Resources\WordpressResource\Pages;

use App\Filament\Resources\WordpressResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Support\Facades\Crypt;

class EditWordpress extends EditRecord
{
    protected static string $resource = WordpressResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }



}

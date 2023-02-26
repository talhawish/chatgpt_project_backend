<?php

namespace App\Filament\Resources\WordpressResource\Pages;

use App\Filament\Resources\WordpressResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWordpresses extends ListRecords
{
    protected static string $resource = WordpressResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

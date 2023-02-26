<?php

namespace App\Filament\Resources\ImportAndGenerateResource\Pages;

use App\Filament\Resources\ImportAndGenerateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImportAndGenerate extends EditRecord
{
    protected static string $resource = ImportAndGenerateResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

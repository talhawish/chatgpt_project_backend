<?php

namespace App\Filament\Resources\JobsResource\Pages;

use Filament\Pages\Actions;
use Illuminate\Support\Facades\Artisan;
use App\Filament\Resources\JobsResource;
use Filament\Resources\Pages\ListRecords;

class ListJobs extends ListRecords
{
    protected static string $resource = JobsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('Start Jobs')->action(function () {
                Artisan::call('queue:listen');
                return true;
            }),
        ];
    }
}

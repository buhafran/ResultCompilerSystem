<?php

namespace App\Filament\School\Resources\SchoolSlideResource\Pages;

use App\Filament\School\Resources\SchoolSlideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchoolSlides extends ListRecords
{
    protected static string $resource = SchoolSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

<?php

namespace App\Filament\School\Resources\SchoolSlideResource\Pages;

use App\Filament\School\Resources\SchoolSlideResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSchoolSlide extends EditRecord
{
    protected static string $resource = SchoolSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}

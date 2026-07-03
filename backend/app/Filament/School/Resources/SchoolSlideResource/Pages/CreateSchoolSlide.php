<?php

namespace App\Filament\School\Resources\SchoolSlideResource\Pages;

use App\Filament\School\Concerns\CreatesTenantRecord;
use App\Filament\School\Resources\SchoolSlideResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSchoolSlide extends CreateRecord
{
    use CreatesTenantRecord;

    protected static string $resource = SchoolSlideResource::class;
}

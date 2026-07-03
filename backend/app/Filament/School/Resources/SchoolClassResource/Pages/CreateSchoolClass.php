<?php
namespace App\Filament\School\Resources\SchoolClassResource\Pages;
use App\Filament\School\Concerns\CreatesTenantRecord;
use App\Filament\School\Resources\SchoolClassResource;
use Filament\Resources\Pages\CreateRecord;
class CreateSchoolClass extends CreateRecord
{
    use CreatesTenantRecord;

    protected static string $resource = SchoolClassResource::class;
}

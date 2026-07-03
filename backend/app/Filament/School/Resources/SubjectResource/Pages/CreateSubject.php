<?php
namespace App\Filament\School\Resources\SubjectResource\Pages;
use App\Filament\School\Concerns\CreatesTenantRecord;
use App\Filament\School\Resources\SubjectResource;
use Filament\Resources\Pages\CreateRecord;
class CreateSubject extends CreateRecord
{
    use CreatesTenantRecord;

    protected static string $resource = SubjectResource::class;
}

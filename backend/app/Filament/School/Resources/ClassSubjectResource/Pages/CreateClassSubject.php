<?php
namespace App\Filament\School\Resources\ClassSubjectResource\Pages;
use App\Filament\School\Concerns\CreatesTenantRecord;
use App\Filament\School\Resources\ClassSubjectResource;
use Filament\Resources\Pages\CreateRecord;
class CreateClassSubject extends CreateRecord
{
    use CreatesTenantRecord;

    protected static string $resource = ClassSubjectResource::class;
}

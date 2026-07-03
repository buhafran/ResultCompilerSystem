<?php
namespace App\Filament\School\Resources\AcademicTermResource\Pages;
use App\Filament\School\Concerns\CreatesTenantRecord;
use App\Filament\School\Resources\AcademicTermResource;
use Filament\Resources\Pages\CreateRecord;
class CreateAcademicTerm extends CreateRecord
{
    use CreatesTenantRecord;

    protected static string $resource = AcademicTermResource::class;
}

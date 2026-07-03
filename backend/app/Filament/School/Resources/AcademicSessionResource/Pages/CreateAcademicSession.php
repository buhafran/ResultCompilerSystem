<?php
namespace App\Filament\School\Resources\AcademicSessionResource\Pages;
use App\Filament\School\Concerns\CreatesTenantRecord;
use App\Filament\School\Resources\AcademicSessionResource;
use Filament\Resources\Pages\CreateRecord;
class CreateAcademicSession extends CreateRecord
{
    use CreatesTenantRecord;

    protected static string $resource = AcademicSessionResource::class;
}

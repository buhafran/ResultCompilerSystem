<?php
namespace App\Filament\School\Resources\TeacherAssignmentResource\Pages;
use App\Filament\School\Concerns\CreatesTenantRecord;
use App\Filament\School\Resources\TeacherAssignmentResource;
use Filament\Resources\Pages\CreateRecord;
class CreateTeacherAssignment extends CreateRecord
{
    use CreatesTenantRecord;

    protected static string $resource = TeacherAssignmentResource::class;
}

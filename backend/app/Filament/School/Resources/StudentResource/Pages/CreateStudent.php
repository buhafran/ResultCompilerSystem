<?php
namespace App\Filament\School\Resources\StudentResource\Pages;
use App\Filament\School\Concerns\CreatesTenantRecord;
use App\Filament\School\Resources\StudentResource;
use Filament\Resources\Pages\CreateRecord;
class CreateStudent extends CreateRecord
{
    use CreatesTenantRecord;

    protected static string $resource = StudentResource::class;
}

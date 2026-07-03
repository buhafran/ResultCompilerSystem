<?php
namespace App\Filament\School\Resources\ResultTemplateResource\Pages;
use App\Filament\School\Concerns\CreatesTenantRecord;
use App\Filament\School\Resources\ResultTemplateResource;
use Filament\Resources\Pages\CreateRecord;
class CreateResultTemplate extends CreateRecord
{
    use CreatesTenantRecord;

    protected static string $resource = ResultTemplateResource::class;
}

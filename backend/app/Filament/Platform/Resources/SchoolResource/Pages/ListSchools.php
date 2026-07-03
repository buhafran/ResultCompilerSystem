<?php
namespace App\Filament\Platform\Resources\SchoolResource\Pages;
use App\Filament\Platform\Resources\SchoolResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListSchools extends ListRecords { protected static string $resource = SchoolResource::class; protected function getHeaderActions(): array { return [CreateAction::make()]; } }

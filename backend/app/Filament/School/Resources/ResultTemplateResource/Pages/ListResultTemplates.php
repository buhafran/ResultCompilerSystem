<?php
namespace App\Filament\School\Resources\ResultTemplateResource\Pages;
use App\Filament\School\Resources\ResultTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListResultTemplates extends ListRecords { protected static string $resource=ResultTemplateResource::class; protected function getHeaderActions():array{return [CreateAction::make()];} }

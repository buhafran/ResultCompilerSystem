<?php
namespace App\Filament\School\Resources\ResultTemplateResource\Pages;
use App\Filament\School\Resources\ResultTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditResultTemplate extends EditRecord { protected static string $resource=ResultTemplateResource::class; protected function getHeaderActions():array{return [DeleteAction::make()];} }

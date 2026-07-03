<?php
namespace App\Filament\School\Resources\ClassSubjectResource\Pages;
use App\Filament\School\Resources\ClassSubjectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditClassSubject extends EditRecord { protected static string $resource = ClassSubjectResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()]; } }

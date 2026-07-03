<?php
namespace App\Filament\School\Resources\ClassSubjectResource\Pages;
use App\Filament\School\Resources\ClassSubjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListClassSubjects extends ListRecords { protected static string $resource = ClassSubjectResource::class; protected function getHeaderActions(): array { return [CreateAction::make()]; } }

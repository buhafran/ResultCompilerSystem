<?php
namespace App\Filament\School\Resources\SubjectResource\Pages;
use App\Filament\School\Resources\SubjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListSubjects extends ListRecords { protected static string $resource = SubjectResource::class; protected function getHeaderActions(): array { return [CreateAction::make()]; } }

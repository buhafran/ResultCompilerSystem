<?php
namespace App\Filament\School\Resources\TeacherAssignmentResource\Pages;
use App\Filament\School\Resources\TeacherAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListTeacherAssignments extends ListRecords { protected static string $resource=TeacherAssignmentResource::class; protected function getHeaderActions():array{return [CreateAction::make()];} }

<?php
namespace App\Filament\School\Resources\TeacherAssignmentResource\Pages;
use App\Filament\School\Resources\TeacherAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditTeacherAssignment extends EditRecord { protected static string $resource=TeacherAssignmentResource::class; protected function getHeaderActions():array{return [DeleteAction::make()];} }

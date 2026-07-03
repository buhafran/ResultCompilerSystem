<?php
namespace App\Filament\School\Resources\AcademicSessionResource\Pages;
use App\Filament\School\Resources\AcademicSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditAcademicSession extends EditRecord { protected static string $resource = AcademicSessionResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()]; } }

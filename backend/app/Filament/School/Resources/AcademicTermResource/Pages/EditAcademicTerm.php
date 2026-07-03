<?php
namespace App\Filament\School\Resources\AcademicTermResource\Pages;
use App\Filament\School\Resources\AcademicTermResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditAcademicTerm extends EditRecord { protected static string $resource = AcademicTermResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()]; } }

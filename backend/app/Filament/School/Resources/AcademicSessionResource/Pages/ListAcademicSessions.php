<?php
namespace App\Filament\School\Resources\AcademicSessionResource\Pages;
use App\Filament\School\Resources\AcademicSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListAcademicSessions extends ListRecords { protected static string $resource = AcademicSessionResource::class; protected function getHeaderActions(): array { return [CreateAction::make()]; } }

<?php
namespace App\Filament\School\Resources\AcademicTermResource\Pages;
use App\Filament\School\Resources\AcademicTermResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListAcademicTerms extends ListRecords { protected static string $resource = AcademicTermResource::class; protected function getHeaderActions(): array { return [CreateAction::make()]; } }

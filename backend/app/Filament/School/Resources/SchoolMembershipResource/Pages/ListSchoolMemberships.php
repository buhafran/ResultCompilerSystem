<?php
namespace App\Filament\School\Resources\SchoolMembershipResource\Pages;
use App\Filament\School\Resources\SchoolMembershipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListSchoolMemberships extends ListRecords { protected static string $resource = SchoolMembershipResource::class; protected function getHeaderActions(): array { return [CreateAction::make()]; } }

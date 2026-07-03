<?php
namespace App\Filament\School\Resources\SchoolMembershipResource\Pages;
use App\Filament\School\Resources\SchoolMembershipResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditSchoolMembership extends EditRecord { protected static string $resource = SchoolMembershipResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()]; } }

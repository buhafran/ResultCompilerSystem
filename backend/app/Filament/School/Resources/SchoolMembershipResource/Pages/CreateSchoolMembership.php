<?php

namespace App\Filament\School\Resources\SchoolMembershipResource\Pages;

use App\Filament\School\Concerns\CreatesTenantRecord;
use App\Filament\School\Resources\SchoolMembershipResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateSchoolMembership extends CreateRecord
{
    use CreatesTenantRecord {
        mutateFormDataBeforeCreate as addTenantToFormData;
    }

    protected static string $resource = SchoolMembershipResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->addTenantToFormData($data);

        $alreadyAssigned = User::query()
            ->whereKey($data['user_id'] ?? null)
            ->whereHas('memberships')
            ->exists();

        if ($alreadyAssigned) {
            throw ValidationException::withMessages([
                'data.user_id' => 'This user already belongs to a school. A platform super administrator must grant any additional school access.',
            ]);
        }

        return $data;
    }
}

<?php
namespace App\Policies;
use App\Models\ResultPublication;
use App\Models\User;
class ResultPublicationPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, ResultPublication $publication): bool { return $user->canAccessTenant($publication->school); }
    public function create(User $user): bool { return $user->is_super_admin || $user->schools()->wherePivotIn('role',['school_admin','exam_officer'])->exists(); }
    public function update(User $user, ResultPublication $publication): bool { return $user->isSchoolManager($publication->school_id); }
}

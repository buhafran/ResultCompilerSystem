<?php

namespace App\Policies;

use App\Models\ResultEntry;
use App\Models\TeacherAssignment;
use App\Models\User;

class ResultEntryPolicy
{
    public function view(User $user, ResultEntry $entry): bool
    {
        $school = $entry->school;
        if (! $school || ! $user->canAccessTenant($school)) {
            return false;
        }

        return $user->isSchoolManager($entry->school_id) || TeacherAssignment::query()
            ->where('school_id', $entry->school_id)
            ->where('user_id', $user->id)
            ->where('academic_term_id', $entry->academic_term_id)
            ->where('school_class_id', $entry->school_class_id)
            ->where('subject_id', $entry->subject_id)
            ->exists();
    }

    public function update(User $user, ResultEntry $entry): bool
    {
        return $this->view($user, $entry);
    }
}

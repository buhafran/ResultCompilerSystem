<?php

namespace App\Enums;

enum MembershipRole: string
{
    case SchoolAdmin = 'school_admin';
    case ExamOfficer = 'exam_officer';
    case Teacher = 'teacher';

    public function label(): string
    {
        return match ($this) {
            self::SchoolAdmin => 'School Administrator',
            self::ExamOfficer => 'Examination Officer',
            self::Teacher => 'Teacher',
        };
    }
}

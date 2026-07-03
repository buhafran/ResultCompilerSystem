<?php

namespace App\Filament\School\Widgets;

use App\Enums\PublicationStatus;
use App\Models\AcademicTerm;
use App\Models\ResultEntry;
use App\Models\ResultPublication;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SchoolOverviewStats extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        $school = Filament::getTenant();

        return $school && (bool) auth()->user()?->isSchoolManager($school);
    }

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $school = Filament::getTenant();
        if (! $school) {
            return [];
        }

        $term = AcademicTerm::query()
            ->with('academicSession:id,name')
            ->where('school_id', $school->id)
            ->where('is_active', true)
            ->first();

        $studentCount = $school->students()->where('is_active', true)->count();
        $subjectCount = $school->subjects()->where('is_active', true)->count();
        $classCount = $school->classes()->where('is_active', true)->count();

        $publicationQuery = ResultPublication::query()->where('school_id', $school->id);
        $scoreCompletion = null;
        if ($term) {
            $expectedScores = DB::table('students')
                ->join('class_subjects', function ($join): void {
                    $join->on('class_subjects.school_class_id', '=', 'students.school_class_id')
                        ->on('class_subjects.school_id', '=', 'students.school_id');
                })
                ->where('students.school_id', $school->id)
                ->where('students.is_active', true)
                ->whereNull('students.deleted_at')
                ->count();

            $completedScores = ResultEntry::query()
                ->where('school_id', $school->id)
                ->where('academic_term_id', $term->id)
                ->where('status', '!=', 'not_entered')
                ->whereHas('student', fn ($query) => $query->where('is_active', true))
                ->count();

            $scoreCompletion = $expectedScores > 0
                ? min(100, round(($completedScores / $expectedScores) * 100))
                : 0;
            $publicationQuery->where('academic_term_id', $term->id);
        }

        $released = (clone $publicationQuery)->where('status', PublicationStatus::Released->value)->count();
        $compiled = (clone $publicationQuery)->where('status', PublicationStatus::Compiled->value)->count();

        return [
            Stat::make('Current term', $term ? "{$term->academicSession->name} · {$term->name}" : 'Not configured')
                ->description($term?->is_locked ? 'Score entry is locked' : ($term ? 'Open for score entry' : 'Create and activate a term'))
                ->descriptionIcon($term?->is_locked ? 'heroicon-m-lock-closed' : 'heroicon-m-calendar-days')
                ->color($term ? ($term->is_locked ? 'warning' : 'success') : 'danger'),
            Stat::make('Active students', number_format($studentCount))
                ->description(number_format($classCount).' active classes')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            Stat::make('Subjects', number_format($subjectCount))
                ->description($scoreCompletion === null ? 'Activate a term to track entry' : "{$scoreCompletion}% score sheets completed")
                ->descriptionIcon('heroicon-m-book-open')
                ->color($scoreCompletion === 100 ? 'success' : 'info'),
            Stat::make('Result publication', "{$released} released")
                ->description("{$compiled} compiled and awaiting release")
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color($compiled > 0 ? 'warning' : ($released > 0 ? 'success' : 'gray')),
        ];
    }
}

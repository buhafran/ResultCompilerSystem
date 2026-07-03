<?php

namespace App\Filament\School\Widgets;

use App\Enums\PublicationStatus;
use App\Models\AcademicTerm;
use App\Models\ResultPublication;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class ResultStatusChart extends ChartWidget
{
    public static function canView(): bool
    {
        $school = Filament::getTenant();

        return $school && (bool) auth()->user()?->isSchoolManager($school);
    }

    protected ?string $heading = 'Current-term publication status';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $school = Filament::getTenant();
        $term = $school ? AcademicTerm::query()->where('school_id', $school->id)->where('is_active', true)->first() : null;

        $counts = collect(PublicationStatus::cases())->mapWithKeys(fn (PublicationStatus $status): array => [
            $status->value => $school && $term
                ? ResultPublication::query()->where('school_id', $school->id)->where('academic_term_id', $term->id)->where('status', $status->value)->count()
                : 0,
        ]);

        return [
            'datasets' => [[
                'label' => 'Publications',
                'data' => $counts->values()->all(),
                'backgroundColor' => ['#94a3b8', '#f59e0b', '#10b981', '#ef4444'],
            ]],
            'labels' => ['Draft', 'Compiled', 'Released', 'Withdrawn'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

<?php

namespace App\Filament\School\Widgets;

use App\Enums\PublicationStatus;
use App\Models\AcademicTerm;
use App\Models\ResultPublication;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class ClassPerformanceChart extends ChartWidget
{
    public static function canView(): bool
    {
        $school = Filament::getTenant();

        return $school && (bool) auth()->user()?->isSchoolManager($school);
    }

    protected ?string $heading = 'Latest class averages';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $school = Filament::getTenant();
        $term = $school ? AcademicTerm::query()->where('school_id', $school->id)->where('is_active', true)->first() : null;

        if (! $school || ! $term) {
            return ['datasets' => [['label' => 'Average (%)', 'data' => []]], 'labels' => []];
        }

        $latestIds = ResultPublication::query()
            ->selectRaw('MAX(id) AS id')
            ->where('school_id', $school->id)
            ->where('academic_term_id', $term->id)
            ->whereIn('status', [PublicationStatus::Compiled->value, PublicationStatus::Released->value])
            ->groupBy('school_class_id')
            ->pluck('id');

        $publications = ResultPublication::query()
            ->with('schoolClass:id,name')
            ->whereIn('id', $latestIds)
            ->orderBy('school_class_id')
            ->get();

        return [
            'datasets' => [[
                'label' => 'Average (%)',
                'data' => $publications->map(fn (ResultPublication $publication): float => (float) data_get($publication->statistics, 'class_average', 0))->all(),
                'backgroundColor' => '#0f766e',
            ]],
            'labels' => $publications->map(fn (ResultPublication $publication): string => $publication->schoolClass->name)->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

<?php

namespace App\Filament\Platform\Widgets;

use App\Models\School;
use Filament\Widgets\ChartWidget;

class SchoolActiveStudentsChart extends ChartWidget
{
    protected ?string $heading = 'Active students by school';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $schools = School::query()
            ->withCount(['students as active_students_count' => fn ($query) => $query->where('is_active', true)])
            ->where('is_active', true)
            ->orderByDesc('active_students_count')
            ->limit(15)
            ->get();

        return [
            'datasets' => [[
                'label' => 'Active students',
                'data' => $schools->pluck('active_students_count')->map(fn ($count): int => (int) $count)->all(),
            ]],
            'labels' => $schools->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

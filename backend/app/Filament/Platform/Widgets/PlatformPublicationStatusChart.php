<?php

namespace App\Filament\Platform\Widgets;

use App\Enums\PublicationStatus;
use App\Models\ResultPublication;
use Filament\Widgets\ChartWidget;

class PlatformPublicationStatusChart extends ChartWidget
{
    protected ?string $heading = 'Result publication status';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $counts = collect(PublicationStatus::cases())->mapWithKeys(fn (PublicationStatus $status): array => [
            $status->value => ResultPublication::query()->where('status', $status->value)->count(),
        ]);

        return [
            'datasets' => [[
                'label' => 'Publications',
                'data' => $counts->values()->all(),
            ]],
            'labels' => $counts->keys()->map(fn (string $status): string => str($status)->headline()->toString())->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

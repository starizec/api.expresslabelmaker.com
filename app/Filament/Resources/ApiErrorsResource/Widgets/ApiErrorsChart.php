<?php

namespace App\Filament\Resources\ApiErrorsResource\Widgets;

use App\Models\ApiError;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ApiErrorsChart extends ChartWidget
{
    protected static ?string $heading = 'API Errors Over Time';

    protected function getData(): array
    {
        $errors = ApiError::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'API Errors',
                    'data' => $errors->pluck('count')->toArray(),
                    'backgroundColor' => '#f43f5e',
                    'borderColor' => '#f43f5e',
                ]
            ],
            'labels' => $errors->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
} 
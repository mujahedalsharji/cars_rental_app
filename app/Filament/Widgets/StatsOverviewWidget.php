<?php

namespace App\Filament\Widgets;

use App\Models\Car;
use App\Models\Category;
use App\Models\Faq;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Cars', Car::count()),
            Stat::make('Published Cars', Car::published()->count()),
            Stat::make('Categories', Category::count()),
            Stat::make('FAQs', Faq::count()),
        ];
    }
}

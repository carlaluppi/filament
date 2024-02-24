<?php

namespace App\Filament\Widgets;

use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // posicion 1, este widget arriba
    protected static ?int $sort =1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Classes', Classes::count()),
            Stat::make('Total sections', Section::count()),
            Stat::make('Total students', Student::count()),
        ];
    }
}

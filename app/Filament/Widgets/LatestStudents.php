<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestStudents extends BaseWidget
{
    //pone la tabla debajo, por eso el 2, el 1 para el otro widget
    protected static ?int $sort =2;

    //darle ancho total a la table, que salia cortada
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
            Student::query()
                ->latest()
                ->limit(5),
            )
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('class.name')->badge()->searchable()->sortable(),
                TextColumn::make('section.name')->badge()->searchable()->sortable(),
            ]);
    }
}

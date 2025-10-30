<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Classes;
use App\Models\Student;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestStudents extends BaseWidget
{
    // menempatkan widget ini di urutan kedua
     protected static ?int $sort = 2;

    // Define the column span for the widget
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                 TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('class.name')->badge() ->searchable(),
                TextColumn::make('section.name')->badge(),
            ]);
    }
}

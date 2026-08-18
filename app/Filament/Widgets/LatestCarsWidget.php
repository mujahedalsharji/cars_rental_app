<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Cars\CarResource;
use App\Models\Car;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestCarsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Car::query()
                    ->with('category')
                    ->latest()
                    ->limit(10)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Car Name'),

                TextColumn::make('category.name')
                    ->label('Category'),

                ToggleColumn::make('is_published')
                    ->label('Published'),
            ])
            ->recordUrl(
                fn (Car $record): string => CarResource::getUrl('edit', ['record' => $record]),
            );
    }
}

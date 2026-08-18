<?php

namespace App\Filament\Resources\Cars\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Car Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('brand')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                ToggleColumn::make('is_published')
                    ->label('Published'),

                ToggleColumn::make('is_featured')
                    ->label('Featured'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(fn () => Category::active()->ordered()->pluck('name', 'id')),

                TernaryFilter::make('is_published')
                    ->label('Status')
                    ->trueLabel('Published')
                    ->falseLabel('Draft'),

                TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->trueLabel('Featured')
                    ->falseLabel('Not Featured'),
            ])
            ->reorderRecordsUsing('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

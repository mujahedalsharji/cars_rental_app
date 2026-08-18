<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('FAQ Details')
                    ->schema([
                        TextInput::make('question')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('answer')
                            ->required()
                            ->rows(5),

                        TextInput::make('category')
                            ->nullable()
                            ->maxLength(100),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}

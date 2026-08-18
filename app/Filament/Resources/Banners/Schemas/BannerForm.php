<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Banner Details')
                    ->schema([
                        TextInput::make('title')
                            ->nullable()
                            ->maxLength(255),

                        TextInput::make('subtitle')
                            ->nullable()
                            ->maxLength(255),

                        FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->directory('banners')
                            ->required(),

                        TextInput::make('cta_text')
                            ->label('CTA Text')
                            ->nullable()
                            ->maxLength(100),

                        TextInput::make('cta_url')
                            ->label('CTA URL')
                            ->nullable()
                            ->maxLength(255),

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

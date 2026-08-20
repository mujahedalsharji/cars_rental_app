<?php

namespace App\Filament\Resources\Cars\Schemas;

use App\Models\Category;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Basic Info ────────────────────────────────────────────────
                Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Car Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from name. Do not change after publishing.'),

                        Select::make('category_id')
                            ->label('Category')
                            ->required()
                            ->options(fn () => Category::active()->ordered()->pluck('name', 'id')),

                        TextInput::make('brand')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('model')
                            ->label('Model')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('year')
                            ->required()
                            ->numeric()
                            ->minValue(1990)
                            ->maxValue((int) date('Y') + 2),

                        TextInput::make('color')
                            ->maxLength(100)
                            ->nullable(),

                        RichEditor::make('description')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                // ── Specifications ────────────────────────────────────────────
                Section::make('Specifications')
                    ->description('Enter key-value pairs. Examples: Engine, Transmission, Seats, Fuel Type, Drive Type.')
                    ->schema([
                        KeyValue::make('specifications')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                // ── Features ──────────────────────────────────────────────────
                Section::make('Features')
                    ->description('Feature tags displayed on the car detail page.')
                    ->schema([
                        Repeater::make('features')
                            ->relationship()
                            ->schema([
                                TextInput::make('feature')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g. Air Conditioning'),
                            ])
                            ->addActionLabel('Add Feature')
                            ->columnSpanFull(),
                    ]),

                // ── Images ────────────────────────────────────────────────────
                Section::make('Images')
                    ->description('First image in order will be used as the cover image.')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('images')
                            ->collection('car_images')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->maxFiles(20)
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file): string => Str::uuid().'.'.Str::lower($file->getClientOriginalExtension())
                            )
                            ->columnSpanFull(),
                    ]),

                // ── Pricing (internal) ────────────────────────────────────────
                Section::make('Pricing (Internal Reference Only)')
                    ->description('⚠ These prices are NOT displayed on the public website. They are for internal admin use only.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price_daily')
                            ->label('Daily Price')
                            ->numeric()
                            ->nullable()
                            ->minValue(0),

                        TextInput::make('price_weekly')
                            ->label('Weekly Price')
                            ->numeric()
                            ->nullable()
                            ->minValue(0),

                        TextInput::make('price_monthly')
                            ->label('Monthly Price')
                            ->numeric()
                            ->nullable()
                            ->minValue(0),

                        Select::make('currency')
                            ->options(['AED' => 'AED', 'USD' => 'USD', 'EUR' => 'EUR', 'SAR' => 'SAR'])
                            ->default('AED')
                            ->required(),
                    ]),

                // ── Publication ───────────────────────────────────────────────
                Section::make('Publication & SEO')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),

                        Toggle::make('is_featured')
                            ->label('Featured on Homepage')
                            ->default(false),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),

                        TextInput::make('meta_title')
                            ->label('SEO Title')
                            ->maxLength(255)
                            ->nullable(),

                        Textarea::make('meta_description')
                            ->label('SEO Description')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

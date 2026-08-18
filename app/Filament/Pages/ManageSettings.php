<?php

namespace App\Filament\Pages;

use App\Services\SettingService;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Manage Settings';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(SettingService $settingService): void
    {
        $allSettings = $settingService->all();

        $flatData = [];
        foreach ($allSettings as $group => $settings) {
            foreach ($settings as $key => $value) {
                $flatData[$group.'_'.$key] = $value;
            }
        }

        $this->form->fill($flatData);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make('Company')
                            ->schema([
                                TextInput::make('company_name')->label('Name')->maxLength(255),
                                TextInput::make('company_tagline')->label('Tagline')->maxLength(255),
                                Textarea::make('company_description')->label('Description'),
                                FileUpload::make('company_logo')
                                    ->label('Logo')
                                    ->image()
                                    ->disk('public')
                                    ->directory('settings'),
                                Textarea::make('company_about_text')->label('About Text'),
                            ]),

                        Tabs\Tab::make('Contact')
                            ->schema([
                                TextInput::make('contact_phone_primary')->label('Phone Primary')->maxLength(100),
                                TextInput::make('contact_phone_secondary')->label('Phone Secondary')->maxLength(100),
                                TextInput::make('contact_email')->label('Email')->email()->maxLength(255),
                                Textarea::make('contact_address')->label('Address'),
                                TextInput::make('contact_whatsapp')->label('WhatsApp Number')->maxLength(100),
                            ]),

                        Tabs\Tab::make('Social')
                            ->schema([
                                TextInput::make('social_facebook')->label('Facebook URL')->url()->maxLength(255),
                                TextInput::make('social_instagram')->label('Instagram URL')->url()->maxLength(255),
                                TextInput::make('social_twitter')->label('Twitter URL')->url()->maxLength(255),
                                TextInput::make('social_youtube')->label('YouTube URL')->url()->maxLength(255),
                                TextInput::make('social_tiktok')->label('TikTok URL')->url()->maxLength(255),
                                TextInput::make('social_linkedin')->label('LinkedIn URL')->url()->maxLength(255),
                            ]),

                        Tabs\Tab::make('SEO')
                            ->schema([
                                TextInput::make('seo_site_title')->label('Site Title')->maxLength(255),
                                Textarea::make('seo_meta_description')->label('Meta Description'),
                                TextInput::make('seo_meta_keywords')->label('Meta Keywords')->maxLength(500),
                                TextInput::make('seo_google_analytics_id')->label('Google Analytics ID')->maxLength(100),
                            ]),

                        Tabs\Tab::make('Appearance')
                            ->schema([
                                FileUpload::make('appearance_favicon')
                                    ->label('Favicon')
                                    ->image()
                                    ->disk('public')
                                    ->directory('settings'),
                                ColorPicker::make('appearance_primary_color')->label('Primary Color'),
                                ColorPicker::make('appearance_secondary_color')->label('Secondary Color'),
                            ]),

                        Tabs\Tab::make('System')
                            ->schema([
                                Toggle::make('system_maintenance_mode')->label('Maintenance Mode'),
                                Select::make('system_app_locale')
                                    ->label('App Locale')
                                    ->options([
                                        'en' => 'English',
                                        'ar' => 'Arabic',
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(SettingService $settingService): void
    {
        $state = $this->form->getState();

        $bulkUpdate = [];
        foreach ($state as $fieldKey => $value) {
            // Convert something like company_name back to company.name
            $parts = explode('_', $fieldKey, 2);
            if (count($parts) === 2) {
                $dbKey = $parts[0].'.'.$parts[1];
                $bulkUpdate[$dbKey] = $value;
            }
        }

        $settingService->setBulk($bulkUpdate);

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}

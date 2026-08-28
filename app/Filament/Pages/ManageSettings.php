<?php

namespace App\Filament\Pages;

use App\Services\SettingService;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, string> */
    private const SETTING_FIELDS = [
        'company_name' => 'company.name',
        'company_tagline' => 'company.tagline',
        'company_description' => 'company.description',
        'company_logo' => 'company.logo',
        'company_about_text' => 'company.about_text',
        'contact_phone_primary' => 'contact.phone_primary',
        'contact_phone_secondary' => 'contact.phone_secondary',
        'contact_email' => 'contact.email',
        'contact_address' => 'contact.address',
        'contact_whatsapp_number' => 'contact.whatsapp_number',
        'social_facebook_url' => 'social.facebook_url',
        'social_instagram_url' => 'social.instagram_url',
        'social_twitter_url' => 'social.twitter_url',
        'social_youtube_url' => 'social.youtube_url',
        'social_tiktok_url' => 'social.tiktok_url',
        'social_linkedin_url' => 'social.linkedin_url',
        'seo_site_title' => 'seo.site_title',
        'seo_meta_description' => 'seo.meta_description',
        'seo_meta_keywords' => 'seo.meta_keywords',
        'seo_google_analytics_id' => 'seo.google_analytics_id',
        'appearance_favicon' => 'appearance.favicon',
        'appearance_primary_color' => 'appearance.primary_color',
        'appearance_secondary_color' => 'appearance.secondary_color',
        'system_maintenance_mode' => 'system.maintenance_mode',
        'system_app_locale' => 'system.app_locale',
    ];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Manage Settings';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(SettingService $settingService): void
    {
        $formData = [];

        foreach (self::SETTING_FIELDS as $field => $settingKey) {
            $formData[$field] = $settingService->get($settingKey);
        }

        $this->form->fill($formData);
    }

    public function form(Schema $form): Schema
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
                                    ->directory('settings')
                                    ->maxSize(5120)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->getUploadedFileNameForStorageUsing(
                                        fn (TemporaryUploadedFile $file): string => Str::uuid().'.'.Str::lower($file->getClientOriginalExtension())
                                    ),
                                Textarea::make('company_about_text')->label('About Text'),
                            ]),

                        Tabs\Tab::make('Contact')
                            ->schema([
                                TextInput::make('contact_phone_primary')->label('Phone Primary')->maxLength(100),
                                TextInput::make('contact_phone_secondary')->label('Phone Secondary')->maxLength(100),
                                TextInput::make('contact_email')->label('Email')->email()->maxLength(255),
                                Textarea::make('contact_address')->label('Address'),
                                TextInput::make('contact_whatsapp_number')->label('WhatsApp Number')->maxLength(100),
                            ]),

                        Tabs\Tab::make('Social')
                            ->schema([
                                TextInput::make('social_facebook_url')->label('Facebook URL')->url()->maxLength(255),
                                TextInput::make('social_instagram_url')->label('Instagram URL')->url()->maxLength(255),
                                TextInput::make('social_twitter_url')->label('Twitter URL')->url()->maxLength(255),
                                TextInput::make('social_youtube_url')->label('YouTube URL')->url()->maxLength(255),
                                TextInput::make('social_tiktok_url')->label('TikTok URL')->url()->maxLength(255),
                                TextInput::make('social_linkedin_url')->label('LinkedIn URL')->url()->maxLength(255),
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
                                    ->directory('settings')
                                    ->maxSize(5120)
                                    ->acceptedFileTypes(['image/x-icon', 'image/png', 'image/svg+xml'])
                                    ->getUploadedFileNameForStorageUsing(
                                        fn (TemporaryUploadedFile $file): string => Str::uuid().'.'.Str::lower($file->getClientOriginalExtension())
                                    ),
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
        try {
            $state = $this->form->getState();
        } catch (FilesystemException $exception) {
            report($exception);

            Notification::make()
                ->title('File upload failed')
                ->body('The server could not store the file. Check the storage directory permissions and try again.')
                ->danger()
                ->send();

            return;
        }

        $bulkUpdate = [];
        foreach (self::SETTING_FIELDS as $field => $settingKey) {
            if (array_key_exists($field, $state)) {
                $bulkUpdate[$settingKey] = $state[$field];
            }
        }

        $settingService->setBulk($bulkUpdate);

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}

<x-filament-panels::page>
    <form
        wire:submit="save"
        x-data="{ isUploadingFile: false }"
        x-on:livewire-upload-start.window="isUploadingFile = true"
        x-on:livewire-upload-finish.window="isUploadingFile = false"
        x-on:livewire-upload-error.window="isUploadingFile = false"
        x-on:livewire-upload-cancel.window="isUploadingFile = false"
    >
        {{ $this->form }}

        <div class="fi-form-actions">
            <x-filament::button
                type="submit"
                x-bind:disabled="isUploadingFile"
                wire:loading.attr="disabled"
            >
                <span x-show="! isUploadingFile">Save</span>
                <span x-show="isUploadingFile" x-cloak>Uploading file...</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>

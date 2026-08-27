<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('guests are redirected to the Filament login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('non administrators cannot access the Filament panel', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

test('administrators can manage dashboard accounts', function () {
    $administrator = User::factory()->admin()->create();

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($administrator)
        ->get(UserResource::getUrl())
        ->assertSuccessful();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Second Administrator',
            'email' => 'second-admin@example.com',
            'password' => 'strong-password',
            'is_admin' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $createdAdministrator = User::query()->where('email', 'second-admin@example.com')->firstOrFail();

    expect($createdAdministrator->is_admin)->toBeTrue()
        ->and(Hash::check('strong-password', $createdAdministrator->password))->toBeTrue();
});

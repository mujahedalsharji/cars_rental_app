<?php

use App\Models\Car;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('car media keeps the first ordered image as the only cover', function () {
    Storage::fake('public');

    $car = Car::factory()->for(Category::factory())->create();
    $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);

    $first = $car
        ->addMedia(UploadedFile::fake()->createWithContent('first.png', $imageContent))
        ->toMediaCollection('car_images');
    $second = $car
        ->addMedia(UploadedFile::fake()->createWithContent('second.png', $imageContent))
        ->toMediaCollection('car_images');

    expect($first->refresh()->getCustomProperty('is_cover'))->toBeTrue()
        ->and($second->refresh()->getCustomProperty('is_cover'))->toBeFalse();

    $second->order_column = 0;
    $second->save();

    expect($second->refresh()->getCustomProperty('is_cover'))->toBeTrue()
        ->and($first->refresh()->getCustomProperty('is_cover'))->toBeFalse();

    $secondPath = $second->getPathRelativeToRoot();
    $second->delete();

    expect($first->refresh()->getCustomProperty('is_cover'))->toBeTrue();
    Storage::disk('public')->assertMissing($secondPath);
});

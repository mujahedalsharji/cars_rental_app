<?php

test('trip numbers increase for every WhatsApp message request', function () {
    $firstTripNumber = $this->postJson(route('trip-numbers.store'))
        ->assertSuccessful()
        ->json('trip_number');

    $secondTripNumber = $this->postJson(route('trip-numbers.store'))
        ->assertSuccessful()
        ->json('trip_number');

    expect($secondTripNumber)->toBe($firstTripNumber + 1);
});

test('trip number endpoint does not accept a number supplied by the user', function () {
    $tripNumber = $this->postJson(route('trip-numbers.store'), ['trip_number' => 999999])
        ->assertSuccessful()
        ->json('trip_number');

    expect($tripNumber)->not->toBe(999999);

    $this->assertDatabaseHas('trip_numbers', ['id' => $tripNumber]);
});

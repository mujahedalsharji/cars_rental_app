<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TripNumberService
{
    public function reserve(): int
    {
        $timestamp = now();

        return (int) DB::table('trip_numbers')->insertGetId([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}

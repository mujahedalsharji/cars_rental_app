<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the unused image and icon columns from the categories table.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['icon', 'image']);
        });
    }

    /**
     * Restore the columns if the migration is rolled back.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->string('icon', 100)->nullable()->after('description');
            $table->string('image', 500)->nullable()->after('icon');
        });
    }
};

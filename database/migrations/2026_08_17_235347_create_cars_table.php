<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->year('year');
            $table->string('color', 100)->nullable();
            $table->longText('description')->nullable();
            $table->json('specifications')->nullable();
            $table->decimal('price_daily', 10, 2)->nullable()->unsigned();
            $table->decimal('price_weekly', 10, 2)->nullable()->unsigned();
            $table->decimal('price_monthly', 10, 2)->nullable()->unsigned();
            $table->string('currency', 3)->default('AED');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->index('category_id');
            $table->index('is_published');
            $table->index('is_featured');
            $table->index('sort_order');
            $table->index('brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};

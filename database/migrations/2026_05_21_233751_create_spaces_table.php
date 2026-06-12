// database/migrations/2024_01_01_000002_create_spaces_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['private', 'coworking', 'meeting','formation','terrace']);
            $table->string('capacity');
            $table->decimal('price', 10, 2);
            $table->text('description');
            $table->json('amenities');
            $table->string('featured_image');
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
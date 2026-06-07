<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            $table->string('duration_type'); // hourly, 2_hours, half_day, daily, weekly, monthly, yearly
            $table->decimal('price', 10, 2);
            $table->integer('duration_hours')->nullable();
            $table->timestamps();
            
            $table->unique(['space_id', 'duration_type']);
            $table->index('duration_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_options');
    }
};
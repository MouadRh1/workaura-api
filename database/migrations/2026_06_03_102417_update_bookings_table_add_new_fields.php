<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Ajouter end_date pour les réservations longues
            $table->date('end_date')->nullable()->after('booking_date');
            
            // Ajouter duration_type
            $table->enum('duration_type', [
                'hourly', '2_hours', 'half_day', 'daily', 'weekly', 'monthly', 'yearly'
            ])->default('hourly')->after('end_time');
            
            // Ajouter unit_price
            $table->decimal('unit_price', 10, 2)->nullable()->after('duration_type');
            
            // Rendre start_time et end_time nullable
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
            
            // Ajouter des index
            $table->index('end_date');
            $table->index('duration_type');
            $table->index(['space_id', 'booking_date']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['end_date', 'duration_type', 'unit_price']);
            $table->dropIndex(['end_date']);
            $table->dropIndex(['duration_type']);
            $table->dropIndex(['space_id', 'booking_date']);
            
            // Revenir à non-nullable
            $table->time('start_time')->nullable(false)->change();
            $table->time('end_time')->nullable(false)->change();
        });
    }
};
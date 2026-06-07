<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            
            // Utilisateur (nullable pour les réservations sans compte)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Espace réservé
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            
            // Informations client (pour les non connectés)
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->string('guest_token', 64)->nullable()->unique();
            
            // Période de réservation
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            
            // Statuts
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            
            // Montant total
            $table->decimal('total_amount', 10, 2);
            
            // Informations supplémentaires
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Index pour les recherches fréquentes
            $table->index('booking_date');
            $table->index('status');
            $table->index('guest_email');
            $table->index('guest_token');
            
            // Empêcher les doubles réservations sur le même créneau
            $table->unique(['space_id', 'booking_date', 'start_time', 'status'], 'unique_active_booking')
                ->whereIn('status', ['pending', 'confirmed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
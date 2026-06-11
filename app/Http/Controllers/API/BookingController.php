<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Space;
use App\Models\PricingOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Obtenir les options de prix pour un espace
     */
    public function getPricingOptions($spaceId)
    {
        $space = Space::findOrFail($spaceId);
        $pricingOptions = PricingOption::where('space_id', $spaceId)->get();
        
        // Ajouter les informations de disponibilité pour les 7 prochains jours
        $availability = $this->getAvailability($spaceId);
        
        return response()->json([
            'space' => $space,
            'pricing_options' => $pricingOptions,
            'availability' => $availability,
            'duration_types' => PricingOption::DURATION_TYPES
        ]);
    }

    /**
     * Créer une réservation (sans authentification requise)
     */
    public function store(Request $request)
    {
        // Validation des règles de base
        $rules = [
            'space_id' => 'required|exists:spaces,id',
            'duration_type' => 'required|in:hourly,2_hours,half_day,daily,weekly,monthly,yearly',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
            'student_discount' => 'nullable|boolean',
            'room_size' => 'nullable|in:small,large',
        ];

        // Règles spécifiques selon le type de durée
        if (in_array($request->duration_type, ['hourly', '2_hours', 'half_day', 'daily'])) {
            $rules['booking_date'] = 'required|date|after_or_equal:today';
            $rules['start_time'] = 'required|date_format:H:i|after_or_equal:08:00|before:20:00';
        } else {
            $rules['start_date'] = 'required|date|after_or_equal:today';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        $space = Space::findOrFail($request->space_id);
        
        // Récupérer le prix selon la durée choisie
        $pricingOption = PricingOption::where('space_id', $request->space_id)
            ->where('duration_type', $request->duration_type)
            ->first();
            
        if (!$pricingOption) {
            return response()->json([
                'message' => 'Option de tarification non disponible pour cet espace'
            ], 422);
        }
        
        // Calculer le montant total selon le type de durée
        $totalAmount = $this->calculateTotalAmount($request, $pricingOption);
        
        // Vérifier la disponibilité selon le type de réservation
        if (!$this->checkAvailability($request, $space)) {
            return response()->json([
                'message' => 'Le créneau demandé n\'est pas disponible'
            ], 422);
        }
        
        // Générer un token unique pour la réservation
        $token = Str::random(64);
        
        // Vérifier si l'utilisateur est connecté
        $user = $request->user();
        $userId = $user ? $user->id : null;
        
        // Si l'utilisateur est connecté, utiliser ses informations
        $guestName = $user ? $user->name : $request->guest_name;
        $guestEmail = $user ? $user->email : $request->guest_email;
        $guestPhone = $user ? ($user->phone ?? $request->guest_phone) : $request->guest_phone;
        
        // Appliquer réduction étudiant si applicable (20%)
        $discount = 0;
        if ($request->has('student_discount') && $request->student_discount) {
            $discount = $totalAmount * 0.20;
            $totalAmount = $totalAmount - $discount;
        }
        
        // Préparer les données de réservation
        $bookingData = [
            'user_id' => $userId,
            'space_id' => $request->space_id,
            'duration_type' => $request->duration_type,
            'total_amount' => $totalAmount,
            'unit_price' => $pricingOption->price,
            'notes' => $request->notes,
            'status' => 'pending',
            'payment_status' => 'pending',
            'guest_name' => $guestName,
            'guest_email' => $guestEmail,
            'guest_phone' => $guestPhone,
            'guest_token' => $token,
        ];

        // Ajouter room_size seulement s'il est présent
        if ($request->has('room_size')) {
            $bookingData['room_size'] = $request->room_size;
        }
        
        // Ajouter les champs spécifiques selon le type
        if (in_array($request->duration_type, ['hourly', '2_hours', 'half_day', 'daily'])) {
            $bookingData['booking_date'] = $request->booking_date;
            $bookingData['start_time'] = $request->start_time;
            $bookingData['end_time'] = $this->calculateEndTime($request->start_time, $pricingOption->duration_hours ?? 1);
            $bookingData['end_date'] = null;
        } else {
            $bookingData['booking_date'] = $request->start_date;
            $bookingData['end_date'] = $request->end_date;
            $bookingData['start_time'] = '00:00:00';
            $bookingData['end_time'] = '23:59:59';
        }
        
        $booking = Booking::create($bookingData);
        
        return response()->json([
            'booking' => $booking->load('space'),
            'token' => $token,
            'discount_applied' => $discount > 0 ? $discount : null,
            'room_size' => $request->room_size,
            'message' => 'Réservation créée avec succès.',
            'confirmation_url' => url("/api/bookings/{$token}/confirm")
        ], 201);
    }
    
    /**
     * Calculer le montant total selon le type de durée
     */
    private function calculateTotalAmount($request, $pricingOption)
    {
        // Pour les réservations à la journée ou à l'heure, prix fixe
        if (in_array($request->duration_type, ['hourly', '2_hours', 'half_day', 'daily'])) {
            return $pricingOption->price;
        }
        
        // Pour les réservations longue durée
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $days = $startDate->diffInDays($endDate) + 1;
        
        switch ($request->duration_type) {
            case 'weekly':
                $weeks = ceil($days / 7);
                return $pricingOption->price * $weeks;
            case 'monthly':
                $months = ceil($days / 30);
                return $pricingOption->price * $months;
            case 'yearly':
                $years = ceil($days / 365);
                return $pricingOption->price * $years;
            default:
                return $pricingOption->price;
        }
    }
    
    /**
     * Calculer l'heure de fin
     */
    private function calculateEndTime($startTime, $durationHours)
    {
        if (!$durationHours) {
            return null;
        }
        return Carbon::parse($startTime)->addHours($durationHours)->format('H:i:s');
    }
    
    /**
     * Vérifier la disponibilité
     */
    private function checkAvailability($request, $space)
    {
        // Pour les réservations courtes (heure, demi-journée, journée)
        if (in_array($request->duration_type, ['hourly', '2_hours', 'half_day', 'daily'])) {
            $endTime = $this->calculateEndTime($request->start_time, 
                PricingOption::where('space_id', $request->space_id)
                    ->where('duration_type', $request->duration_type)
                    ->first()->duration_hours ?? 1);
                    
            return !Booking::where('space_id', $request->space_id)
                ->where('booking_date', $request->booking_date)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($request, $endTime) {
                    $query->where(function ($q) use ($request, $endTime) {
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $request->start_time);
                    });
                })
                ->exists();
        }
        
        // Pour les réservations longue durée
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        return !Booking::where('space_id', $request->space_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('booking_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($sub) use ($startDate, $endDate) {
                          $sub->where('booking_date', '<=', $startDate)
                               ->where('end_date', '>=', $endDate);
                      });
                });
            })
            ->exists();
    }
    
    /**
     * Obtenir la disponibilité pour les 7 prochains jours
     */
    private function getAvailability($spaceId)
    {
        $availability = [];
        $startDate = Carbon::today();
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $bookings = Booking::where('space_id', $spaceId)
                ->where('booking_date', $date->format('Y-m-d'))
                ->where('status', '!=', 'cancelled')
                ->get();
            
            // Créneaux disponibles (de 8h à 20h)
            $slots = [];
            for ($hour = 8; $hour < 20; $hour++) {
                $start = sprintf('%02d:00', $hour);
                $end = sprintf('%02d:00', $hour + 1);
                $isAvailable = true;
                
                foreach ($bookings as $booking) {
                    $bookingStart = Carbon::parse($booking->start_time)->format('H:i');
                    if ($bookingStart === $start) {
                        $isAvailable = false;
                        break;
                    }
                }
                
                $slots[] = [
                    'start' => $start,
                    'end' => $end,
                    'available' => $isAvailable
                ];
            }
            
            $availability[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->locale('fr')->dayName,
                'day_number' => $date->format('d'),
                'month' => $date->locale('fr')->monthName,
                'available_slots' => $slots
            ];
        }
        
        return $availability;
    }

    /**
     * Confirmer une réservation par token (sans authentification)
     */
    public function confirm($token)
    {
        $booking = Booking::where('guest_token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid'
        ]);

        return response()->json([
            'message' => 'Réservation confirmée avec succès',
            'booking' => $booking->load('space')
        ]);
    }

    /**
     * Voir le statut d'une réservation (sans authentification)
     */
    public function status($token)
    {
        $booking = Booking::where('guest_token', $token)
            ->with('space')
            ->firstOrFail();

        return response()->json([
            'booking' => $booking,
            'status' => $booking->status,
            'status_label' => $this->getStatusLabel($booking->status),
            'payment_status' => $booking->payment_status,
            'payment_status_label' => $this->getPaymentStatusLabel($booking->payment_status)
        ]);
    }

    /**
     * Mes réservations (pour utilisateur connecté)
     */
    public function myBookings(Request $request)
    {
        $user = $request->user();

        $bookings = Booking::where('guest_email', $user->email)
            ->orWhere('user_id', $user->id)
            ->with('space')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($bookings);
    }

    /**
     * Voir une réservation spécifique (pour utilisateur connecté)
     */
    public function show($id, Request $request)
    {
        $user = $request->user();

        $booking = Booking::with('space')
            ->where(function ($query) use ($user, $id) {
                $query->where('id', $id)
                    ->where(function ($q) use ($user) {
                        $q->where('guest_email', $user->email)
                            ->orWhere('user_id', $user->id);
                    });
            })
            ->firstOrFail();

        return response()->json($booking);
    }

    /**
     * Annuler une réservation (pour utilisateur connecté)
     */
    public function cancel($id, Request $request)
    {
        $user = $request->user();

        $booking = Booking::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('guest_email', $user->email)
                    ->orWhere('user_id', $user->id);
            })
            ->where('status', 'pending')
            ->firstOrFail();

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Réservation annulée avec succès'
        ]);
    }

    /**
     * Annuler une réservation par token (pour les invités)
     */
    public function cancelByToken($token, Request $request)
    {
        $booking = Booking::where('guest_token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Réservation annulée avec succès'
        ]);
    }

    /**
     * Obtenir le libellé du statut
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'cancelled' => 'Annulée'
        ];
        return $labels[$status] ?? $status;
    }

    /**
     * Obtenir le libellé du statut de paiement
     */
    private function getPaymentStatusLabel($status)
    {
        $labels = [
            'pending' => 'En attente',
            'paid' => 'Payé',
            'failed' => 'Échoué'
        ];
        return $labels[$status] ?? $status;
    }
}
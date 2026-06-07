<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Space;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SpaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Space::where('is_active', true);
        
        // Filtrage par type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Tri
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        $spaces = $query->paginate(9);
        
        // Charger les images pour chaque espace
        $spaces->load('images');
        
        // Ajouter l'URL complète des images
        foreach ($spaces as $space) {
            if ($space->featured_image && !str_starts_with($space->featured_image, 'http')) {
                $space->featured_image_url = url($space->featured_image);
            } else {
                $space->featured_image_url = $space->featured_image;
            }
        }
        
        return response()->json([
            'spaces' => $spaces,
            'message' => 'Liste des espaces'
        ]);
    }

    public function show($slug)
    {
        $space = Space::where('slug', $slug)
            ->with('images')
            ->firstOrFail();
        
        // Ajouter l'URL complète de l'image principale
        if ($space->featured_image && !str_starts_with($space->featured_image, 'http')) {
            $space->featured_image_url = url($space->featured_image);
        } else {
            $space->featured_image_url = $space->featured_image;
        }
        
        // Ajouter les URLs complètes pour les images secondaires
        foreach ($space->images as $image) {
            if ($image->image_path && !str_starts_with($image->image_path, 'http')) {
                $image->image_url = url($image->image_path);
            } else {
                $image->image_url = $image->image_path;
            }
        }
        
        // Récupérer les disponibilités pour les 7 prochains jours
        $availabilities = [];
        $startDate = Carbon::today();
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $bookings = Booking::where('space_id', $space->id)
                ->where('booking_date', $date->format('Y-m-d'))
                ->where('status', '!=', 'cancelled')
                ->get();
            
            $availabilities[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->locale('fr')->dayName,
                'available_slots' => $this->getAvailableSlots($bookings)
            ];
        }
        
        // Ajouter les options de prix (si vous avez la table pricing_options)
        $pricingOptions = [];
        if (method_exists($space, 'pricingOptions')) {
            $pricingOptions = $space->pricingOptions()->get();
        }
        
        return response()->json([
            'space' => $space,
            'availabilities' => $availabilities,
            'pricing_options' => $pricingOptions
        ]);
    }

    private function getAvailableSlots($bookings)
    {
        // Créneaux de 1 heure de 10h à 22h
        $allSlots = [];
        for ($hour = 10; $hour < 22; $hour++) {
            $start = sprintf('%02d:00', $hour);
            $end = sprintf('%02d:00', $hour + 1);
            $isAvailable = true;
            
            foreach ($bookings as $booking) {
                $bookingStart = Carbon::parse($booking->start_time)->format('H:i');
                $bookingEnd = Carbon::parse($booking->end_time)->format('H:i');
                
                // Vérifier si le créneau chevauche
                if (($bookingStart <= $start && $bookingEnd > $start) ||
                    ($bookingStart < $end && $bookingEnd >= $end) ||
                    ($bookingStart >= $start && $bookingEnd <= $end)) {
                    $isAvailable = false;
                    break;
                }
            }
            
            $allSlots[] = [
                'start' => $start,
                'end' => $end,
                'available' => $isAvailable
            ];
        }
        
        return $allSlots;
    }

    public function checkAvailability(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time'
        ]);
        
        $isAvailable = Booking::isAvailable(
            $id,
            $request->date,
            $request->start_time,
            $request->end_time
        );
        
        return response()->json([
            'available' => $isAvailable,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time
        ]);
    }
    
    /**
     * Get spaces by type
     */
    public function getByType($type)
    {
        $validTypes = ['private', 'coworking', 'meeting', 'formation'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json([
                'message' => 'Type d\'espace invalide',
                'valid_types' => $validTypes
            ], 400);
        }
        
        $spaces = Space::where('is_active', true)
            ->where('type', $type)
            ->with('images')
            ->orderBy('sort_order')
            ->get();
        
        return response()->json([
            'spaces' => $spaces,
            'type' => $type,
            'count' => $spaces->count()
        ]);
    }
    
    /**
     * Search spaces
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type');
        
        $spaces = Space::where('is_active', true)
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->with('images')
            ->orderBy('sort_order')
            ->paginate(12);
        
        return response()->json([
            'spaces' => $spaces,
            'search' => $query,
            'type' => $type
        ]);
    }
}
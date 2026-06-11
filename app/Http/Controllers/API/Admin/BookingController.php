<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'space']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date')) {
            $query->whereDate('booking_date', $request->date);
        }
        
        $bookings = $query->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(20);
        
        
        return response()->json($bookings);
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled'
        ]);
        
        $booking->update(['status' => $request->status]);
        
        return response()->json([
            'booking' => $booking->load('user', 'space'),
            'message' => 'Statut mis à jour'
        ]);
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();
        
        return response()->json(['message' => 'Réservation supprimée']);
    }
}
<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Space;
use App\Models\Booking;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
    }

    public function stats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('role', 'user')->count(),
            'total_spaces' => Space::count(),
            'available_spaces' => Space::where('status', 'available')->count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'total_revenue' => Booking::where('status', 'confirmed')->sum('total_amount'),
            'gallery_images' => Gallery::count(),
            'monthly_bookings' => Booking::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get(),
            'recent_bookings' => Booking::with(['user', 'space'])
                ->latest()
                ->limit(10)
                ->get()
        ];
        
        return response()->json($stats);
    }
}
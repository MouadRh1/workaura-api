<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = Gallery::where('is_active', true);
        
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        $galleries = $query->orderBy('sort_order')
            ->paginate(12);
        
        return response()->json($galleries);
    }

    public function show($slug)
    {
        $gallery = Gallery::where('slug', $slug)->firstOrFail();
        return response()->json($gallery);
    }
}
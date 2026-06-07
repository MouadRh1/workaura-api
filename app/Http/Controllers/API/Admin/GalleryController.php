<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $galleries = Gallery::orderBy('sort_order')->paginate(15);
        return response()->json($galleries);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10120',
            'category' => 'required|in:space,event,community'
        ]);

        $image = $request->file('image');
        $imageName = time() . '_' . Str::slug($request->title) . '.' . $image->extension();
        $image->move(public_path('uploads/gallery'), $imageName);

        $gallery = Gallery::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . time(),
            'description' => $request->description,
            'image_path' => '/uploads/gallery/' . $imageName,
            'category' => $request->category,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->is_active ?? true
        ]);

        return response()->json([
            'gallery' => $gallery,
            'message' => 'Image ajoutée à la galerie'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);
        
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:10120',
            'category' => 'sometimes|in:space,event,community',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            $oldImage = public_path($gallery->image_path);
            if (file_exists($oldImage)) {
                unlink($oldImage);
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($request->title ?? $gallery->title) . '.' . $image->extension();
            $image->move(public_path('uploads/gallery'), $imageName);
            $request->merge(['image_path' => '/uploads/gallery/' . $imageName]);
        }

        if ($request->has('title') && $request->title !== $gallery->title) {
            $request->merge(['slug' => Str::slug($request->title) . '-' . time()]);
        }

        $gallery->update($request->all());

        return response()->json([
            'gallery' => $gallery,
            'message' => 'Image mise à jour'
        ]);
    }

    public function destroy($id)
    {
        $gallery = Gallery::findOrFail($id);
        
        $imagePath = public_path($gallery->image_path);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        
        $gallery->delete();
        
        return response()->json(['message' => 'Image supprimée']);
    }
}
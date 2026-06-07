<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Space;
use App\Models\SpaceImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SpaceController extends Controller
{
    public function __construct()
    {
        // $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index()
    {
        $spaces = Space::with('images')->orderBy('sort_order')->paginate(10);
        return response()->json($spaces);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:private,coworking,meeting,formation',
            'capacity' => 'required|string',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'amenities' => 'required|array',
            'featured_image' => 'required|image|mimes:jpeg,png,jpg|max:10048',
            'status' => 'required|in:available,occupied,maintenance'
        ]);

        $image = $request->file('featured_image');
        $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->extension();
        $image->move(public_path('uploads/spaces'), $imageName);

        $space = Space::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . time(),
            'type' => $request->type,
            'capacity' => $request->capacity,
            'price' => $request->price,
            'description' => $request->description,
            'amenities' => $request->amenities,
            'featured_image' => '/uploads/spaces/' . $imageName,
            'status' => $request->status,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true
        ]);

        return response()->json([
            'space' => $space,
            'message' => 'Espace créé avec succès'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $space = Space::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:private,coworking,meeting,formation',
            'capacity' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'description' => 'sometimes|string',
            'amenities' => 'sometimes|array',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg|max:10048',
            'status' => 'sometimes|in:available,occupied,maintenance'
        ]);

        $data = [
            'name' => $request->name ?? $space->name,
            'type' => $request->type ?? $space->type,
            'capacity' => $request->capacity ?? $space->capacity,
            'price' => $request->price ?? $space->price,
            'description' => $request->description ?? $space->description,
            'status' => $request->status ?? $space->status,
        ];

        if ($request->has('amenities')) {
            $data['amenities'] = $request->amenities;
        }

        if ($request->hasFile('featured_image')) {
            if ($space->featured_image && file_exists(public_path($space->featured_image))) {
                unlink(public_path($space->featured_image));
            }
            
            $image = $request->file('featured_image');
            $imageName = time() . '_' . Str::slug($request->name ?? $space->name) . '.' . $image->extension();
            $image->move(public_path('uploads/spaces'), $imageName);
            $data['featured_image'] = '/uploads/spaces/' . $imageName;
        }

        $space->update($data);

        if ($request->has('name') && $request->name !== $space->name) {
            $space->slug = Str::slug($request->name) . '-' . time();
            $space->save();
        }

        return response()->json([
            'space' => $space->fresh(),
            'message' => 'Espace mis à jour avec succès'
        ]);
    }

    // =============================================
    // GESTION DES IMAGES MULTIPLES
    // =============================================

    /**
     * Récupérer toutes les images d'un espace
     */
    public function getImages($id)
    {
        try {
            $space = Space::findOrFail($id);
            $images = $space->images()->orderBy('sort_order')->get();
            
            return response()->json([
                'images' => $images,
                'message' => 'Images récupérées avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter une image à un espace
     */
    public function addImage(Request $request, $id)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:10048',
                'is_primary' => 'sometimes|boolean'
            ]);
            
            $space = Space::findOrFail($id);
            
            // Upload l'image
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->extension();
            
            // Créer le dossier s'il n'existe pas
            if (!file_exists(public_path('uploads/spaces/gallery'))) {
                mkdir(public_path('uploads/spaces/gallery'), 0777, true);
            }
            
            $image->move(public_path('uploads/spaces/gallery'), $imageName);
            
            // Récupérer le dernier ordre
            $lastOrder = SpaceImage::where('space_id', $id)->max('sort_order') ?? -1;
            
            $isPrimary = $request->is_primary ?? ($space->images()->count() === 0);
            
            $spaceImage = SpaceImage::create([
                'space_id' => $space->id,
                'image_path' => '/uploads/spaces/gallery/' . $imageName,
                'is_primary' => $isPrimary,
                'sort_order' => $lastOrder + 1
            ]);
            
            // Si c'est l'image principale, retirer le flag des autres
            if ($isPrimary) {
                SpaceImage::where('space_id', $id)
                    ->where('id', '!=', $spaceImage->id)
                    ->update(['is_primary' => false]);
            }
            
            return response()->json([
                'image' => $spaceImage,
                'message' => 'Image ajoutée avec succès'
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'ajout de l\'image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une image (ordre, is_primary)
     */
    public function updateImage(Request $request, $id, $imageId)
    {
        try {
            $request->validate([
                'is_primary' => 'sometimes|boolean',
                'sort_order' => 'sometimes|integer|min:0'
            ]);
            
            $image = SpaceImage::where('space_id', $id)->findOrFail($imageId);
            
            if ($request->has('is_primary') && $request->is_primary) {
                // Retirer le flag primary des autres images
                SpaceImage::where('space_id', $id)
                    ->where('id', '!=', $imageId)
                    ->update(['is_primary' => false]);
            }
            
            $image->update($request->only(['is_primary', 'sort_order']));
            
            return response()->json([
                'image' => $image,
                'message' => 'Image mise à jour avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une image d'un espace
     */
    public function deleteImage($id, $imageId)
    {
        try {
            $image = SpaceImage::where('space_id', $id)->findOrFail($imageId);
            
            $imagePath = public_path($image->image_path);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            $image->delete();
            
            return response()->json([
                'message' => 'Image supprimée avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10048'
        ]);
        
        $space = Space::findOrFail($id);
        $image = $request->file('image');
        $imageName = time() . '_' . uniqid() . '.' . $image->extension();
        $image->move(public_path('uploads/spaces/gallery'), $imageName);
        
        $spaceImage = SpaceImage::create([
            'space_id' => $space->id,
            'image_path' => '/uploads/spaces/gallery/' . $imageName,
            'is_primary' => $request->is_primary ?? false
        ]);
        
        return response()->json([
            'image' => $spaceImage,
            'message' => 'Image ajoutée avec succès'
        ]);
    }

    public function deleteImageLegacy($imageId)
    {
        $image = SpaceImage::findOrFail($imageId);
        
        if (file_exists(public_path($image->image_path))) {
            unlink(public_path($image->image_path));
        }
        
        $image->delete();
        
        return response()->json(['message' => 'Image supprimée avec succès']);
    }

    public function destroy($id)
    {
        $space = Space::findOrFail($id);
        
        if ($space->featured_image && file_exists(public_path($space->featured_image))) {
            unlink(public_path($space->featured_image));
        }
        
        foreach ($space->images as $image) {
            if (file_exists(public_path($image->image_path))) {
                unlink(public_path($image->image_path));
            }
            $image->delete();
        }
        
        $space->delete();
        
        return response()->json(['message' => 'Espace supprimé avec succès']);
    }
}
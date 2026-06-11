<?php
// app/Http/Controllers/API/Admin/ContactController.php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Liste des messages de contact
     */
    public function index(Request $request)
    {
        $query = Contact::query();
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }
        
        $contacts = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json($contacts);
    }

    /**
     * Afficher un message spécifique
     */
    public function show($id)
    {
        $contact = Contact::findOrFail($id);
        
        // Marquer comme lu si ce n'est pas déjà fait
        if ($contact->status === 'pending') {
            $contact->markAsRead();
        }
        
        return response()->json($contact);
    }

    /**
     * Marquer un message comme répondu
     */
    public function markAsReplied($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->markAsReplied();
        
        return response()->json([
            'message' => 'Message marqué comme répondu',
            'contact' => $contact
        ]);
    }

    /**
     * Supprimer un message
     */
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();
        
        return response()->json([
            'message' => 'Message supprimé avec succès'
        ]);
    }

    /**
     * Statistiques des contacts
     */
    public function stats()
    {
        $stats = [
            'total' => Contact::count(),
            'pending' => Contact::pending()->count(),
            'read' => Contact::where('status', 'read')->count(),
            'replied' => Contact::where('status', 'replied')->count(),
            'today' => Contact::whereDate('created_at', today())->count(),
        ];
        
        return response()->json($stats);
    }
}
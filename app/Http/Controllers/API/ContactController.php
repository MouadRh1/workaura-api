<?php
// app/Http/Controllers/API/ContactController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Envoyer un message de contact (public)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:20',
            'message' => 'required|string|min:10|max:5000',
            'conditions' => 'accepted'
        ], [
            'conditions.accepted' => 'Vous devez accepter les conditions générales'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $contact = Contact::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'message' => $request->message,
            'status' => 'pending'
        ]);

        // Envoyer un email de confirmation au client
        // Mail::to($request->email)->send(new ContactConfirmation($contact));

        // Envoyer un email à l'administrateur
        // Mail::to('admin@workaura.com')->send(new NewContactNotification($contact));

        return response()->json([
            'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.',
            'contact' => $contact
        ], 201);
    }
}
<?php
// app/Http/Controllers/API/Admin/EmailController.php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    public function __construct()
    {
    }

    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'cc' => 'nullable|array',
            'cc.*' => 'email',
            'bcc' => 'nullable|array',
            'bcc.*' => 'email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            foreach ($request->recipients as $recipient) {
                Mail::raw($request->message, function ($message) use ($recipient, $request) {
                    $message->to($recipient)
                            ->subject($request->subject)
                            ->from('contact@workaura.ma', 'Workaura');
                });
            }

            return response()->json([
                'message' => 'Emails envoyés avec succès',
                'sent_to' => count($request->recipients) . ' destinataire(s)',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'envoi des emails',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Mail\CreationContactFormMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CreationContactFormController extends Controller
{
    public function creation(Request $request): JsonResponse
    {
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:1000',
            'additional_info' => 'nullable|string|max:255', // Honeypot field for spam prevention
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();

        // Honeypot spam filter - if filled, it's likely spam
        if (! empty($validatedData['additional_info'])) {
            return response()->json([
                'message' => 'Spam detected',
            ], 422);
        }

        try {
            // Send email notification
            Mail::to('penseeboheme76@gmail.com')->send(new CreationContactFormMail($validatedData));

            // Optionally save to database
            // ContactForm::create($validatedData);

            return response()->json([
                'message' => 'Formulaire envoyé avec succès',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'envoi du formulaire',
            ], 500);
        }
    }
}

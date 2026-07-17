<?php

namespace App\Http\Controllers;

use App\Models\CompanyData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Display contact form
     */
    public function showForm()
    {
        return view('contact.form');
    }

    /**
     * Handle contact form submission
     */
    public function submitForm(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:25',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ], [
            'name.required' => 'El nombre es requerido.',
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'phone.required' => 'El teléfono es requerido.',
            'subject.required' => 'El asunto es requerido.',
            'message.required' => 'El mensaje es requerido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Capitalize name
            $name = ucwords(strtolower($request->name));

            $companyData = CompanyData::with('user')->first();
            $companyEmail = $companyData?->email ?: config('mail.contact_fallback');
            $adminEmail = $companyData?->adminEmail();

            // Data array for the email
            $data = [
                'name' => $name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message2' => $request->message,
                'phone' => $request->phone,
            ];

            // Envío a la empresa, con copia interna al administrador
            Mail::send('emails.contactMailForm', $data, function ($message) use ($request, $companyEmail, $adminEmail) {
                $message->replyTo($request->email, $request->name);
                $message->to($companyEmail)->subject('Contacto Web: '.$request->subject);

                if ($adminEmail) {
                    $message->cc($adminEmail);
                }
            });

            // Log success
            Log::info('Contact form email sent successfully', [
                'reply_to' => $request->email,
                'to' => $companyEmail,
                'cc' => $adminEmail,
            ]);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'El formulario se ha enviado correctamente. Nos pondremos en contacto con usted pronto.',
            ]);
        } catch (\Exception $e) {
            // Log error
            Log::error('Error sending contact form email: '.$e->getMessage());

            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'No pudimos enviar su mensaje. Por favor intente de nuevo más tarde o contáctenos directamente por teléfono.',
            ], 500);
        }
    }
}

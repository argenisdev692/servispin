<?php

namespace App\Livewire;

use App\Models\CompanyData;
use Exception;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ContactForm extends Component
{
    public $name;

    public $email;

    public $subject;

    public $message;

    public $phone;

    public function submit()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);

        try {
            $companyData = CompanyData::with('user')->first();
            $companyEmail = $companyData?->email ?: config('mail.contact_fallback');
            $adminEmail = $companyData?->adminEmail();

            // Data array for the email
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'subject' => $this->subject,
                'message2' => $this->message,
                'phone' => $this->phone,
            ];

            // Envío a la empresa, con copia interna al administrador
            Mail::send('emails.contactMailForm', $data, function ($message) use ($companyEmail, $adminEmail) {
                $message->replyTo($this->email, $this->name);
                $message->to($companyEmail)->subject('Contacto Web: '.$this->subject);

                if ($adminEmail) {
                    $message->cc($adminEmail);
                }
            });

            // Restablecer los campos del formulario
            $this->resetForm();

            // Mostrar un mensaje de éxito al usuario
            session()->flash('success', 'El formulario se ha enviado correctamente. Nos pondremos en contacto con usted pronto.');
        } catch (Exception $e) {
            // Log error
            \Log::error('Error sending contact form email: '.$e->getMessage());

            // Show error message to user
            session()->flash('error', 'No pudimos enviar su mensaje. Por favor intente de nuevo más tarde o contáctenos directamente por teléfono.');
        }
    }

    private function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->subject = '';
        $this->message = '';
        $this->phone = '';
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}

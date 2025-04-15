<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use App\Models\CompanyData;
use Exception;

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
            // Get company data for the admin email
            $companyData = CompanyData::first();
            
            if (!$companyData || !$companyData->email) {
                // Fallback admin email if company data isn't set
                $adminEmail = 'info@servispin.com';
            } else {
                $adminEmail = $companyData->email;
            }

            // Data array for the email
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'subject' => $this->subject,
                'message2' => $this->message,
                'phone' => $this->phone,
            ];

            // Send email to admin
            Mail::send('emails.contactMailForm', $data, function($message) use ($adminEmail) {
                $message->from($this->email, $this->name);
                $message->to($adminEmail)->subject('Contacto Web: ' . $this->subject);
            });

            // Restablecer los campos del formulario
            $this->resetForm();

            // Mostrar un mensaje de éxito al usuario
            session()->flash('success', 'El formulario se ha enviado correctamente. Nos pondremos en contacto con usted pronto.');
        } catch (Exception $e) {
            // Log error
            \Log::error('Error sending contact form email: ' . $e->getMessage());
            
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

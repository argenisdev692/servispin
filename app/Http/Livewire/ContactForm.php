<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;

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

        // Enviar el correo electrónico
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
            'phone' => $this->phone,
        ];

        //SEND EMAIL FORM CONTACT
        
\Mail::send('emails.contactMailForm', array(
    'name' => $this->name,
    'email' => $this->email,
    'subject' => $this->subject,
    'message2' => $this->message,
    'phone' => $this->phone,
    
), function($message) {
    $emailAdmin = "aiosrealestate2023@gmail.com";

    $message->from($emailAdmin,'ServiSpin');
    $message->to($this->email)->subject($this->subject);
});
// END SEND EMAIL FORM CONTACT

        // Restablecer los campos del formulario
        $this->resetForm();

        // Mostrar un mensaje de éxito al usuario
        session()->flash('success', 'El formulario se ha enviado correctamente.');
    }

    private function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->subject = '';
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}

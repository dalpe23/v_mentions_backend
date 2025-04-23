<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NuevaAlertaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alertaData; 

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($alertaData)
    {
        $this->alertaData = $alertaData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('➕ 📢 Nueva Alerta a añadir')
            ->markdown('emails.alerta');
    }
}

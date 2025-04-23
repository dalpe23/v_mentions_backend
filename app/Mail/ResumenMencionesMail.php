<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResumenMencionesMail extends Mailable
{
    use Queueable, SerializesModels;

    public $menciones;

    public function __construct($menciones)
    {
        $this->menciones = $menciones;
    }

    public function build()
    {
        return $this->subject('📢 Resumen mensual de menciones negativas y neutras')
                    ->markdown('emails.resumen_menciones');
    }
}

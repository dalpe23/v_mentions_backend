<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NuevaAlertaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alertaData;

    public function __construct($alertaData)
    {
        $this->alertaData = $alertaData;
    }

    public function build()
    {
        $contenido = '
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="https://v-mentions.myp.com.es/VMentionsBlack.png" alt="Logo VMentions" style="max-width: 220px; height: auto;">
        </div>

        <div style="background: #fff; border-radius: 8px; padding: 32px; max-width: 600px; margin: 0 auto; font-family: Arial, Helvetica, sans-serif; color: #222; box-shadow: 0 2px 8px #eee;">
            <h2 style="color: #2d3748;">¡¡Nueva Alerta Recibida Para Añadir!!</h2>
            <p><b>Keywords:</b> ' . e($this->alertaData['keywords']) . '</p>
            <p><b>Idioma:</b> ' . e($this->alertaData['idioma']) . '</p>
            <p><b>ID del usuario:</b> ' . e($this->alertaData['user_id']) . '</p>
            <div style="text-align: center; margin: 32px 0;">
                <a href="https://v-mentions.myp.com.es/alertas" style="background: #3182ce; color: #fff; padding: 12px 32px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 16px;">Ver Alertas</a>
            </div>
            <div style="text-align: right; color: #888; font-size: 14px; margin-top: 32px;">VMentions</div>
        </div>';

        return $this->subject('➕ 📢 Nueva Alerta a añadir')
                    ->html($contenido);
    }
}
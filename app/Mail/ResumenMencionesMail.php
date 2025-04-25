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
    $contenido = '
    <div style="text-align: center; margin-bottom: 24px;">
        <img src="https://v-mentions.myp.com.es/VMentionsBlack.png" alt="Logo VMentions" style="max-width: 220px; height: auto;">
    </div>

    <div style="background: #fff; border-radius: 8px; padding: 32px; max-width: 600px; margin: 0 auto; font-family: Arial, Helvetica, sans-serif; color: #222; box-shadow: 0 2px 8px #eee;">
        <p>A continuación, las menciones registradas con valoración <b>neutra o negativa</b> en el último mes:</p>
        <ul style="padding-left: 18px;">';

    foreach ($this->menciones as $mencion) {
        $contenido .= '
            <li style="margin-bottom: 18px;">
                <b>' . e($mencion->titulo) . '</b><br>
                ' . e(\Illuminate\Support\Str::limit($mencion->descripcion, 120)) . '<br>
                Fuente: ' . e($mencion->fuente) . '<br>
                <a href="' . e($mencion->enlace) . '" style="color: #3182ce;">Ver enlace</a><br>
                Sentimiento: <b>' . e($mencion->sentimiento) . '</b> | Temática: ' . e($mencion->tematica) . '
            </li>';
    }

    $contenido .= '</ul>
        <div style="text-align: right; color: #888; font-size: 14px; margin-top: 32px;">VMentions</div>
    </div>';

    return $this->subject('📢 Resumen mensual de menciones negativas y neutras')
                ->html($contenido);
}
}
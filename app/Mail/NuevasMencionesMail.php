<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NuevasMencionesMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resumenAlertas;
    public $total;
    public $detallesAlertas;

    public function __construct($resumenAlertas, $total, $detallesAlertas = [])
    {
        $this->resumenAlertas = $resumenAlertas;
        $this->total = $total;
        $this->detallesAlertas = $detallesAlertas;
    }

    public function build()
    {
        $contenido = '
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="https://v-mentions.myp.com.es/VMentionsBlack.png" alt="Logo VMentions" style="max-width: 220px; height: auto;">
        </div>
        <div style="background: #fff; border-radius: 8px; padding: 32px; max-width: 600px; margin: 0 auto; font-family: Arial, Helvetica, sans-serif; color: #222; box-shadow: 0 2px 8px #eee;">
            <h2 style="color: #2d3748;">Tienes ' . e($this->total) . ' menciones nuevas</h2>';

        foreach ($this->resumenAlertas as $alertaNombre => $cantidad) {
            $contenido .= '<p>Para la alerta <b>' . e($alertaNombre) . '</b> tienes <b>' . e($cantidad) . '</b> nuevas menciones.';
            if (!empty($this->detallesAlertas[$alertaNombre])) {
                $contenido .= '<ul style="margin: 8px 0 16px 18px;">';
                foreach ($this->detallesAlertas[$alertaNombre] as $detalle) {
                    $contenido .= '<li><b>' . e($detalle['titulo']) . '</b> <span style="color:#888;font-size:13px;">(' . e($detalle['fecha']) . ')</span></li>';
                }
                $contenido .= '</ul>';
            }
            $contenido .= '</p>';
        }

        $contenido .= '<p style="margin-top:24px;">Accede a <a href="https://v-mentions.myp.com.es/alertas" style="color: #3182ce; font-weight: bold;">VMentions</a> para revisarlas.</p>';
        $contenido .= '<div style="text-align: right; color: #888; font-size: 14px; margin-top: 32px;">VMentions</div>';
        $contenido .= '</div>';

        return $this->subject('🔔 Tienes ' . $this->total . ' menciones nuevas en VMentions')
                    ->html($contenido);
    }
}

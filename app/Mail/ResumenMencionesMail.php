<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResumenMencionesMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mencionesAgrupadas;

    public function __construct($menciones)
    {
        $this->mencionesAgrupadas = $menciones->groupBy(function ($mencion) {
            return $mencion->alerta->titulo ?? 'Sin título';
        });
    }

    public function build()
    {
        $contenido = '
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="https://v-mentions.myp.com.es/VMentionsBlack.png" alt="Logo VMentions" style="max-width: 220px; height: auto;">
        </div>

        <div style="background: #fff; border-radius: 8px; padding: 32px; max-width: 600px; margin: 0 auto; font-family: Arial, Helvetica, sans-serif; color: #222; box-shadow: 0 2px 8px #eee;">
            <h2 style="color: #2d3748;">Resumen mensual de menciones negativas y neutras</h2>';

        foreach ($this->mencionesAgrupadas as $tituloAlerta => $menciones) {
            $nombreAlerta = $menciones->first()->alerta->nombre ?? 'sin_keywords';
            $contenido .= '<h3 style="color: #2d3748;">Para la alerta <b>' . e($tituloAlerta) . '</b> con keywords <b>' . e($nombreAlerta) . '</b></h3>';
            $contenido .= '<ul style="margin: 8px 0 16px 18px;">';

            foreach ($menciones as $mencion) {
                $contenido .= '<li style="margin-bottom: 16px;">
                    <b>' . e($mencion->titulo) . '</b><br>
                    <span style="color:#888;font-size:13px;">' . e($mencion->created_at->format('d-m-Y')) . '</span><br>
                    <div><b>Descripción:</b> ' . e($mencion->descripcion) . '</div>
                    <div><b>Fuente:</b> ' . e($mencion->fuente) . '</div>
                    <div><b>Enlace:</b> <a href="' . e($mencion->enlace) . '" style="color: #3182ce;">Ver enlace</a></div>
                    <div><b>Sentimiento:</b> ' . e($mencion->sentimiento) . '</div>
                    <div><b>Temática:</b> ' . e($mencion->tematica) . '</div>
                </li>';
            }

            $contenido .= '</ul>';
        }

        $contenido .= '<div style="text-align: right; color: #888; font-size: 14px; margin-top: 32px;">VMentions</div>
        </div>';

        return $this->subject('📢 Resumen mensual de menciones negativas y neutras')
                    ->html($contenido);
    }
}

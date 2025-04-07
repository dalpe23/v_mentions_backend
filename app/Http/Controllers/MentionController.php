<?php

namespace App\Http\Controllers;

use willvincent\Feeds\Facades\FeedsFacade as Feeds;
use Illuminate\Http\Request;
use App\Models\Mention;

class MentionController extends Controller
{
    /**
     * Obtener y guardar nuevas menciones desde el RSS.
     */
    public function obtenerMenciones()
    {
        $feed = Feeds::make('https://www.google.es/alerts/feeds/17603787138236543600/14698299664815428232', 10);
        $items = $feed->get_items();

        foreach ($items as $item) {
            $titulo = $item->get_title();
            $descripcion = strip_tags($item->get_description());
            $enlace = $item->get_permalink();
            $fecha = $item->get_date('Y-m-d H:i:s');

            Mention::updateOrCreate(
                ['url' => $enlace],
                [
                    'texto' => $titulo . ' ' . $descripcion,
                    'fuente' => 'Google Alerts',
                    'fecha' => $fecha
                ]
            );
        }

        return response()->json(['mensaje' => 'Menciones actualizadas correctamente.']);
    }

    /**
     * Listar todas las menciones.
     */
    public function index()
    {
        return response()->json(Mention::orderBy('fecha', 'desc')->get());
    }

    /**
     * Guardar una mención manualmente (por ejemplo, desde un scraper externo).
     */
    public function store(Request $request)
    {
        $request->validate([
            'texto' => 'required|string',
            'fuente' => 'required|string',
            'url' => 'required|url|unique:mentions,url',
            'fecha' => 'required|date'
        ]);

        Mention::create($request->all());

        return response()->json(['mensaje' => 'Mención guardada correctamente.']);
    }
}

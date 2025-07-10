<?php

namespace App\Http\Controllers;

use App\Models\Alerta;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Mail\NuevaAlertaMail;
use Illuminate\Support\Facades\Mail;


class AlertaController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'keywords' => 'required|string|min:3|max:100',
            'idioma' => 'required|string',
            'ceid' => 'nullable|string',
            'hl' => 'nullable|string',
            'gl' => 'nullable|string',
        ]);

        $user = $request->user();

        $query = urlencode($validatedData['keywords']);
        // Construir la URL RSS de Google News según idioma y país
        $hl = $validatedData['hl'] ?? null;
        $gl = $validatedData['gl'] ?? null;
        $ceid = $validatedData['ceid'] ?? null; 

        // Si no se envían los parámetros, usar por defecto el idioma de la alerta
        if (!$hl || !$gl || !$ceid) {
            switch ($validatedData['idioma']) {
                case 'de':
                    $hl = 'de'; $gl = 'DE'; $ceid = 'DE:de'; break;
                case 'fr':
                    $hl = 'fr'; $gl = 'FR'; $ceid = 'FR:fr'; break;
                case 'it':
                    $hl = 'it'; $gl = 'IT'; $ceid = 'IT:it'; break;
                case 'pt':
                    $hl = 'pt'; $gl = 'PT'; $ceid = 'PT:pt'; break;
                case 'ru':
                    $hl = 'ru'; $gl = 'RU'; $ceid = 'RU:ru'; break;
                case 'zh':
                    $hl = 'zh-CN'; $gl = 'CN'; $ceid = 'CN:zh'; break;
                case 'ja':
                    $hl = 'ja'; $gl = 'JP'; $ceid = 'JP:ja'; break;
                case 'ko':
                    $hl = 'ko'; $gl = 'KR'; $ceid = 'KR:ko'; break;
                case 'ar':
                    $hl = 'ar'; $gl = 'SA'; $ceid = 'SA:ar'; break;
                case 'hi':
                    $hl = 'hi'; $gl = 'IN'; $ceid = 'IN:hi'; break;
                case 'en-GB':
                    $hl = 'en-GB'; $gl = 'GB'; $ceid = 'GB:en'; break;
                case 'en-US':
                    $hl = 'en-US'; $gl = 'US'; $ceid = 'US:en'; break;
                default:
                    $hl = 'es'; $gl = 'ES'; $ceid = 'ES:es';
            }
        }
        $rssUrl = "https://news.google.com/rss/search?q={$query}&hl={$hl}&gl={$gl}&ceid={$ceid}";

        dd($validatedData);

        $alerta = Alerta::create([
            'nombre' => $validatedData['keywords'],
            'idioma' => $validatedData['idioma'],
            'url' => $rssUrl,
            'user_id' => $user->id,
            'resuelta' => false,
        ]);

        return response()->json([
            'message' => 'Alerta creada correctamente.',
            'alerta' => $alerta,
        ], 201);
    }


    /**
     * Devuelve las menciones asociadas a una alerta.
     */
    public function mencionesDeAlerta($id)
    {
        $alerta = Alerta::with('menciones')->find($id);

        if (!$alerta) {
            return response()->json(['error' => 'Alerta no encontrada'], 404);
        }

        return response()->json($alerta->menciones);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Alerta::all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $alerta = Alerta::find($id);

        if (!$alerta) {
            return response()->json(['error' => 'Alerta no encontrada'], 404);
        }

        $alerta->delete();

        return response()->json(['message' => 'Alerta eliminada con éxito']);
    }

    /**
     * Devuelve las alertas asociadas a un user_id específico.
     */
    public function misAlertas(Request $request)
    {
        $user = $request->user();

        $alertas = Alerta::where('user_id', $user->id)->get();

        return response()->json($alertas);
    }

    /**
     * Marca una alerta como resuelta.
     */
    public function marcarComoResuelta($id)
    {
        $alerta = Alerta::find($id);

        if (!$alerta) {
            return response()->json(['error' => 'Alerta no encontrada'], 404);
        }

        $alerta->resuelta = true;
        $alerta->save();

        return response()->json(['message' => 'Alerta marcada como resuelta']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
}

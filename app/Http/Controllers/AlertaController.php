<?php

namespace App\Http\Controllers;
use App\Models\Alerta;

use Illuminate\Http\Request;
use App\Mail\NuevaAlertaMail;
use Illuminate\Support\Facades\Mail;

class AlertaController extends Controller
{
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
    {      //añadir que solo se devuelvan las alertas del cliente logueado


        
        return response()->json(Alerta::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validatedData = $request->validate([
        'keywords' => 'required|string|min:3|max:100',
        'idioma' => 'required|string',
    ]);

    $user = $request->user(); 

    $alertaData = [
        'keywords' => $validatedData['keywords'],
        'idioma' => $validatedData['idioma'],
        'user_id' => $user->id,
    ];

    Mail::to('daniel@mediosyproyectos.com')->send(new NuevaAlertaMail($alertaData));

    return response()->json([
        'message' => 'Alerta creada y correo enviado correctamente.'
    ], 201);
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
}

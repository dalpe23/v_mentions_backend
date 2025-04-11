<?php

namespace App\Http\Controllers;
use App\Models\Alerta;

use Illuminate\Http\Request;

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
        //
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
    public function alertasPorUsuario($id)
    {
        $alertas = Alerta::where('user_id', $id)->get();

        if ($alertas->isEmpty()) {
            return response()->json(['error' => 'No se encontraron alertas para este usuario'], 404);
        }

        return response()->json($alertas);
    }
}

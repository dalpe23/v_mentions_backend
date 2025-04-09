<?php

namespace App\Http\Controllers;
use App\Models\Alerta;

use Illuminate\Http\Request;

class AlertaController extends Controller
{
    /**
     * Devuelve las menciones asociadas a una alerta.
     */
    public function menciones($id)
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
        //
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
    public function destroy(string $id)
    {
        //
    }
}

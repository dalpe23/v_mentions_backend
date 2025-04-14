<?php

namespace App\Http\Controllers;

use App\Models\Mencion;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class MencionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Mencion::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $mencion = Mencion::findOrFail($id);
        return response()->json($mencion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mencion $mencion)
    {
        $mencion->update($request->all());
        return response()->json($mencion);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mencion $mencion)
    {
        //
    }

    /**
     * Obtiene todas las menciones del usuario autenticado.
     */

    public function misMenciones(Request $request)
    {
        $user = $request->user(); 
        
        $menciones = Mencion::whereHas('alerta', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();
        
        return response()->json($menciones);
    }

    public function marcarComoLeida($id)
    {
        try {
            $mencion = Mencion::findOrFail($id);
            $mencion->leida = 1;
            $mencion->save();

            return response()->json(['success' => true, 'message' => 'Mención marcada como leída.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Mención no encontrada.'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al marcar la mención como leída.'], 500);
        }
    }

    public function ponerComoNoLeida($id)
    {
        try {
            $mencion = Mencion::findOrFail($id);
            $mencion->leida = 0;
            $mencion->save();

            return response()->json(['success' => true, 'message' => 'Mención marcada como no leída.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Mención no encontrada.'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al marcar la mención como no leída.'], 500);
        }
    }

    public function cambiarSentimiento(Request $request, $id)
    {
        try {
            $mencion = Mencion::findOrFail($id);
            $mencion->sentimiento = $request->input('sentimiento');
            $mencion->save();

            return response()->json(['success' => true, 'message' => 'Sentimiento cambiado.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Mención no encontrada.'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cambiar el sentimiento.'], 500);
        }
    }

}

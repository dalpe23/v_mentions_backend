<?php

namespace App\Http\Controllers;

use App\Models\Mencion;
use Illuminate\Http\Request;

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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Mencion $mencion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mencion $mencion)
    {
        //
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
        $menciones = Mencion::where('user_id', $user->id)->get();
        return response()->json($menciones);
    }
}

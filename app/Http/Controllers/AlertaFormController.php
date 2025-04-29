<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertaFormController extends Controller
{
    /**
     * Store a newly created alerta in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->rol !== 'admin') {
            return response()->json(['error' => 'No tienes permiso para crear una alerta'], 403);
        }
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $alerta = Alerta::create($validatedData);

        return response()->json([
            'message' => 'Alerta creada correctamente.',
            'alerta' => $alerta
        ], 201);
    }
}

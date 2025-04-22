<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class ClienteController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->rol !== 'admin') {
            return response()->json(['error' => 'No tienes permiso para acceder a esta información'], 403);
        }
        $clientes = User::where('rol', 'cliente')->get();
        return response()->json($clientes);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->rol !== 'admin') {
            return response()->json(['error' => 'No tienes permiso para crear un cliente'], 403);
        }
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
        ]);
        $cliente = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol'      => 'cliente',
        ]);

        return response()->json([
            'message' => 'Cliente creado correctamente',
        ], 201);
    }
    
    /**
     * Elimina un cliente de la base de datos.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->rol !== 'admin') {
            return response()->json(['error' => 'No tienes permiso para eliminar un cliente'], 403);
        }
        $cliente = User::where('rol', 'cliente')->find($id);
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }
}

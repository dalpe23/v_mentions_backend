<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class ClienteController extends Controller
{
    public function index()
    {
        $clientes = User::where('rol', 'cliente')->get();
        return response()->json($clientes);
    }

    public function store(Request $request)
    {
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
        $cliente = User::where('rol', 'cliente')->find($id);
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }
}

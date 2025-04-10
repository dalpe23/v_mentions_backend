<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = User::where('rol', 'cliente')->get();
        return response()->json($clientes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $cliente = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'rol'      => 'cliente', 
        ]);

        return response()->json($cliente, 201);
    }

    public function update(Request $request, $id)
    {
        $cliente = User::findOrFail($id);
        
        $cliente->update($request->only(['name', 'email']));
        return response()->json($cliente);
    }

    public function destroy($id)
    {
        $cliente = User::findOrFail($id);
        $cliente->delete();
        return response()->json(['message' => 'Cliente eliminado correctamente.']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Error en la validación', $validator->errors()->toArray(), 400);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $authUser = Auth::user();

            $token = $authUser->createToken('NombreDelToken')->plainTextToken;

            if (!$token) {
                return $this->sendError('Error al generar el token', ['error' => 'No se pudo generar el token'], 400);
            }

            $result['name'] = $authUser->name;
            $result['id'] = $authUser->id;
            $result['token'] = $token;
            $result['email'] = $authUser->email;
            $result['rol'] = $authUser->rol;


            return $this->sendResponse($result, 'Usuario autenticado correctamente', 200);
        }

        return $this->sendError('No autorizado.', ['error' => 'email o contraseña incorrectos'], 400);
    }

    public function logout(Request $request)
    {
        // Revocar todos los tokens del usuario autenticado
        $request->user()->tokens()->delete();

        // Cerrar sesión
        Auth::logout();

        return $this->sendResponse([], 'Usuario cerrado sesión correctamente', 200);
    }  
}

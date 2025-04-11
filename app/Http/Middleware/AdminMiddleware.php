<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::user() || Auth::user()->role !== 'Administrador') {
            return redirect('/login')->with('error', 'No tienes permiso para acceder a esta pagina.');
        }

        return $next($request);
    }
}
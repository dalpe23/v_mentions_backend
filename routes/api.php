<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MencionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\ClienteController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
| Estas rutas no requieren autenticación.
*/
Route::post('login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Rutas Protegidas por Sanctum
|--------------------------------------------------------------------------
| Todas las rutas dentro de este grupo requieren que el usuario esté autenticado 
| mediante el middleware auth:sanctum.
*/

Route::post('logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    
    // Obtención del usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('alertas', [AlertaController::class, 'store']);


    // Rutas de Menciones (utilizamos apiResource para CRUD completo)
    Route::apiResource('menciones', MencionController::class);
    Route::patch('/menciones/{id}/leida', [MencionController::class, 'marcarComoLeida']);
    Route::patch('/menciones/{id}/ponerComoNoLeida', [MencionController::class, 'ponerComoNoLeida']);
    Route::patch('/menciones/{id}/cambiarSentimiento', [MencionController::class, 'cambiarSentimiento']);

    // Rutas generales de alertas (puedes protegerlas y luego, dentro de los controladores, 
    // validar permisos o mostrar sólo alertas del usuario, según tu lógica de negocio)
    Route::get('/alertas', [AlertaController::class, 'index']);
    Route::get('/alertas/{id}', [AlertaController::class, 'show']);
    Route::post('/alertas', [AlertaController::class, 'store']);
    Route::put('/alertas/{id}', [AlertaController::class, 'update']);
    Route::delete('/alertas/{id}', [AlertaController::class, 'destroy']);
    Route::patch('/alertas/{id}', [AlertaController::class, 'marcarComoResuelta']);

    // Ruta para obtener las menciones de una alerta específica
    Route::get('/alertas/{id}/menciones', [AlertaController::class, 'mencionesDeAlerta']);
    
    // Ruta para obtener alertas por id
    Route::middleware('auth:sanctum')->get('/mis-alertas', [AlertaController::class, 'misAlertas']);
    // Ruta para obtener menciones de un usuario específico
    Route::middleware('auth:sanctum')->get('/mis-menciones', [MencionController::class, 'misMenciones']);


    // Rutas de Clientes:
    Route::apiResource('clientes', ClienteController::class);

});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MencionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\AlertaFormController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AlertEmailController;

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
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/alertas', [AlertaController::class, 'store']);

    Route::post('/alertas-form', [AlertaFormController::class, 'store']);


    Route::apiResource('menciones', MencionController::class);
    Route::patch('/menciones/{id}/leida', [MencionController::class, 'marcarComoLeida']);
    Route::patch('/menciones/{id}/ponerComoNoLeida', [MencionController::class, 'ponerComoNoLeida']);
    Route::patch('/menciones/{id}/cambiarSentimiento', [MencionController::class, 'cambiarSentimiento']);

    Route::get('/alertas', [AlertaController::class, 'index']);
    Route::get('/alertas/{id}', [AlertaController::class, 'show']);
    Route::post('/alertas', [AlertaController::class, 'store']);
    Route::put('/alertas/{id}', [AlertaController::class, 'update']);
    Route::delete('/alertas/{id}', [AlertaController::class, 'destroy']);
    Route::patch('/alertas/{id}', [AlertaController::class, 'marcarComoResuelta']);

    Route::get('/alertas/{id}/menciones', [AlertaController::class, 'mencionesDeAlerta']);
    
    Route::middleware('auth:sanctum')->get('/mis-alertas', [AlertaController::class, 'misAlertas']);
    Route::middleware('auth:sanctum')->get('/mis-menciones', [MencionController::class, 'misMenciones']);


    Route::apiResource('clientes', ClienteController::class);

    Route::get('/alert-emails', [AlertEmailController::class, 'index']);
    Route::post('/alert-emails', [AlertEmailController::class, 'store']);
    Route::delete('/alert-emails/{id}', [AlertEmailController::class, 'destroy']);

});

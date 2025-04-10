<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MencionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\ClienteController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
        

Route::middleware('api')->group(function () {

    // Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);

    Route::apiResource('menciones', MencionController::class);
    
    Route::get('/alertas', [AlertaController::class, 'index']);
    Route::get('/alertas/{id}', [AlertaController::class, 'show']);
    Route::post('/alertas', [AlertaController::class, 'store']);
    Route::put('/alertas/{id}', [AlertaController::class, 'update']);
    Route::delete('/alertas/{id}', [AlertaController::class, 'destroy']);
    Route::get('/alertas/{id}/menciones', [AlertaController::class, 'menciones']);
    Route::get('/clientes', [ClienteController::class, 'index']);
    Route::post('/clientes', [ClienteController::class, 'store']);
    Route::put('/clientes/{id}', [ClienteController::class, 'update']);
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);
});

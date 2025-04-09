<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MencionController;
use App\Http\Controllers\AlertaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('api')->group(function () {
    Route::apiResource('menciones', MencionController::class);

    
    Route::get('/alertas/{id}/menciones', [AlertaController::class, 'menciones']);

});

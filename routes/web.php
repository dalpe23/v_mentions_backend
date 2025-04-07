<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MentionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/menciones', [MentionController::class, 'index']);
Route::post('/menciones', [MentionController::class, 'store']);
Route::get('/menciones/actualizar', [MentionController::class, 'obtenerMenciones']);
<?php
use App\Http\Controllers\MencionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'version' => app()->version(),
    ]);
});


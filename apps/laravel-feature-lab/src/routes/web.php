<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Healthcheck endpoint for Docker
Route::get('/health', fn () => response()->json(['status' => 'ok']));

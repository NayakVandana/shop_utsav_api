<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;



  Route::post('login', [AuthController::class, 'login']);


Route::middleware('auth.token')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/save', [CartController::class, 'store']);
        Route::put('{uuid}', [CartController::class, 'update']);
        Route::delete('{uuid}', [CartController::class, 'destroy']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
    });
});
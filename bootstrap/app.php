<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AuthenticateToken;
use App\Http\Middleware\CheckAdmin;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
     ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('api/user')
                ->group(base_path('routes/user-api.php'));

            Route::middleware('api')
                ->prefix('api/admin')
                ->group(base_path('routes/admin-api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.token' => AuthenticateToken::class,
            'admin' => CheckAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Optional: custom exception handling
    })
    ->create();

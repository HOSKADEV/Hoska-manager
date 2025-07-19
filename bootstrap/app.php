<?php

use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsAdminOrAccountant;
use App\Http\Middleware\isAuth;
use App\Http\Middleware\IsEmployeeOrAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [__DIR__.'/../routes/web.php', __DIR__.'/../routes/admin.php'],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
            $middleware->alias([
            'isAuth' => isAuth::class,
            'IsAdmin' => IsAdmin::class,
            'IsAdminOrAccountant' => IsAdminOrAccountant::class,
            'IsEmployeeOrAdmin' => IsEmployeeOrAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

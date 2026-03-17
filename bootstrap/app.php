<?php

use App\Http\Middleware\SetUserTimezone;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register Spatie role/permission middleware aliases
        $middleware->alias([
            'role' => \Spatie\LaravelPermission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\LaravelPermission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\LaravelPermission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Apply user timezone on every authenticated web request
        $middleware->appendToGroup('web', SetUserTimezone::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

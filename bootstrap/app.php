<?php

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
        // Alias de middleware para SGIC 2.0
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        
        // Excluir rutas de verificación WhatsApp de protección CSRF
        $middleware->validateCsrfTokens(except: [
            'inventory/commercial/contracts/send-verification-code',
            'inventory/commercial/contracts/verify-code',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
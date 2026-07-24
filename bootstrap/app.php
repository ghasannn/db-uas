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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\SuperadminMiddleware::class,
            'superadmin' => \App\Http\Middleware\SuperadminMiddleware::class,
            'organizer' => \App\Http\Middleware\OrganizerMiddleware::class,
            'organizer.approved' => \App\Http\Middleware\CheckOrganizerApproved::class,
        ]);
        $middleware->redirectTo(
            guests: function (\Illuminate\Http\Request $request) {
                if ($request->is('admin*') || $request->routeIs('admin.*')) {
                    return route('admin.login');
                }
                return route('login');
            }
        );
        $middleware->validateCsrfTokens(except: [
            '/midtrans/callback',
            '/midtrans/callback/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
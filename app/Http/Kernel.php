<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Auth\Middleware\Authenticate;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\SubstituteBindings;

class Kernel extends HttpKernel
{
    protected $middleware = [
    ];

    protected $middlewareGroups = [
        'web' => [
            // …
        ],

        'api' => [
            'throttle:api',
            SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'auth'         => Authenticate::class,
        'auth:sanctum' => Authenticate::class,
        'throttle'     => ThrottleRequests::class,
        'admin'        => AdminMiddleware::class,
    ];
}

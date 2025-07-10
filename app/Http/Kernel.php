<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // ...other properties...

    protected $routeMiddleware = [
        // ...other middleware...
        'custom.auth' => \App\Http\Middleware\SessionAuth::class,
    ];
}
<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersion
{
    public function handle(Request $request, Closure $next, string $version = 'v1'): Response
    {
        $request->attributes->set('api_version', $version);
        return $next($request);
    }
}

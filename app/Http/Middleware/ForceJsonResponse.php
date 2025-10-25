<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
          if ($request->is('api/*') || $request->is('api/auth/*')) {
        // Force the request to expect JSON
        $request->headers->set('Accept', 'application/json');
          }
        $response = $next($request);

          if ($request->is('api/*') || $request->is('api/auth/*')) {
        // Optionally, ensure response content type is JSON
        if (method_exists($response, 'header')) {
            $response->header('Content-Type', 'application/json');
        }
    }
        return $response;
    }
}

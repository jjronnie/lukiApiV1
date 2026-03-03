<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJsonOnly
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->expectsJson() && ! $request->is('api/*')) {
            return $next($request);
        }

        if (! $request->expectsJson() && $request->is('api/*')) {
            return new JsonResponse([
                'message' => 'Only JSON requests are supported.',
            ], 406);
        }

        return $next($request);
    }
}

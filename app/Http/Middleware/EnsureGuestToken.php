<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuestToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('sanctum')->check()) {
            return $next($request);
        }

        $token = $request->cookie('guest_token');

        if (! Str::isUuid($token)) {
            $token = (string) Str::uuid();

            $request->attributes->set('guest_token', $token);

            return $next($request)
                ->withCookie(cookie('guest_token', $token, 60 * 24 * 365));
        }

        $request->attributes->set('guest_token', $token);

        return $next($request);
    }
}

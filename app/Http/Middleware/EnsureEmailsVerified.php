<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() &&
            $request->user()->isUser() &&
            !$request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Your email address is not verified.'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

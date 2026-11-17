<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->status !== UserStatus::ACTIVE) {
            return response()->json([
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }
        return $next($request);
    }
}

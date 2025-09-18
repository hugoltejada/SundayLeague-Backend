<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPhoneKey
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization') ?? $request->input('token');

        if (!$token || $token !== config('app.phone_key')) {
            return response()->json([
                'error' => true,
                'message' => 'Token not found',
            ], 401);
        }

        return $next($request);
    }
}

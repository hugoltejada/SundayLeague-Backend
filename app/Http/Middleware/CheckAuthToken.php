<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Phone;

class CheckAuthToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken() ?? $request->input('token');

        if (!$token) {
            return response()->json([
                'error' => true,
                'message' => 'Missing APP token',
            ], 401);
        }

        // Buscar el teléfono por auth_token
        $phone = Phone::where('auth_token', $token)->first();

        if (!$phone) {
            return response()->json([
                'error' => true,
                'message' => 'Invalid APP token',
            ], 401);
        }

        // Inyectamos el phone en la request
        $request->attributes->set('phone', $phone);

        return $next($request);
    }
}

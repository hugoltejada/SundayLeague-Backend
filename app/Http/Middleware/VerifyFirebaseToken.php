<?php

namespace App\Http\Middleware;

use Closure;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Illuminate\Http\Request;

class VerifyFirebaseToken
{
    protected Auth $auth;

    public function __construct()
    {
        $this->auth = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
            ->withProjectId(env('FIREBASE_PROJECT_ID'))
            ->createAuth();
    }

    public function handle(Request $request, Closure $next)
    {
        $idToken = $request->bearerToken();

        if (!$idToken) {
            return response()->json(['error' => true, 'message' => 'No token provided'], 401);
        }

        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);

            // 👇 claims() siempre devuelve array asociativo plano
            $claims = $verifiedIdToken->claims()->all();

            $uid   = $claims['sub']   ?? null;
            $email = $claims['email'] ?? null;

            \Log::info("[Middleware] UID={$uid}, Email={$email}");

            $request->attributes->set('firebase_id', $uid);
            $request->attributes->set('firebase_email', $email);
        } catch (\Exception $e) {
            \Log::error('[Middleware] Invalid token: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'Invalid or expired token'], 401);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PhoneRegisterEmailRequest;
use App\Http\Requests\PhoneVerifyEmailRequest;
use App\Http\Requests\PhoneVerifyGoogleRequest;
use App\Models\Phone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PhoneConfirmationCodeMail;
use Carbon\Carbon;
use Kreait\Firebase\Factory;
use Illuminate\Support\Str;


use Google_Client;

class PhoneController extends Controller
{
    /**
     * Registro con email
     */
    public function registerEmail(PhoneRegisterEmailRequest $request)
    {
        $data = $request->validated();

        $phone = Phone::create([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'password'           => Hash::make($data['password']),
            'platform'           => $data['platform'] ?? null,
            'notification_token' => $data['notification_token'] ?? null,
            'auth'               => false,
            'auth_code'          => str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            'auth_token'         => Str::random(60),
        ]);

        // Crear Player y Supporter asociados
        $phone->player()->create([
            'name' => $phone->name,
        ]);

        $phone->supporter()->create([
            'nickname' => $phone->name,
        ]);

        // enviar email con código
        Mail::to($phone->email)->send(new PhoneConfirmationCodeMail($phone));

        return response()->json([
            'error'      => false,
            'message'    => 'Verification code sent',
            'auth_token' => $phone->auth_token,
        ], 200);
    }

    /**
     * Verificación de email con código
     */
    public function verifyEmail(PhoneVerifyEmailRequest $request)
    {
        $data = $request->validated();

        $phone = Phone::where('email', $data['email'])
            ->where('auth_code', $data['auth_code'])
            ->first();

        if (!$phone) {
            return response()->json(['error' => true, 'message' => 'Invalid code'], 404);
        }

        if ($phone->auth) {
            return response()->json(['error' => true, 'message' => 'Already verified'], 409);
        }

        $phone->update([
            'auth'          => true,
            'authorized_at' => Carbon::now(),
        ]);

        return response()->json(['error' => false, 'message' => 'Verified'], 200);
    }

    /**
     * Registro con Google
     */
    public function registryGoogle(PhoneVerifyGoogleRequest $request)
    {
        $data = $request->validated();

        try {
            $firebase = (new Factory)
                ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
            $auth = $firebase->createAuth();

            $verifiedIdToken = $auth->verifyIdToken($data['id_token']);
            $claims = $verifiedIdToken->claims();

            $email = $claims->get('email');
            $name  = $claims->get('name');
            $googleId = $claims->get('sub');


            if (!$email) {
                return response()->json(['error' => true, 'message' => 'Firebase token missing email'], 422);
            }

            $phone = Phone::withTrashed()->where('email', $email)->first();

            if (!$phone) {
                $phone = Phone::create([
                    'name'               => $name,
                    'email'              => $email,
                    'google_id'          => $googleId,
                    'password'           => null,
                    'platform'           => $data['platform'] ?? null,
                    'notification_token' => $data['notification_token'] ?? null,
                    'auth'               => true,
                    'authorized_at'      => now(),
                    'auth_token'         => Str::random(60),
                ]);
                // Crear Player y Supporter asociados
                $phone->player()->create([
                    'name' => $phone->name,
                ]);

                $phone->supporter()->create([
                    'nickname' => $phone->name,
                ]);
            } else {
                $phone->restore();
                $phone->update([
                    'name'               => $name,
                    'google_id'          => $googleId,
                    'platform'           => $data['platform'] ?? null,
                    'notification_token' => $data['notification_token'] ?? null,
                    'auth'               => true,
                    'authorized_at'      => now(),
                ]);
            }

            return response()->json([
                'error'   => false,
                'message' => 'Google user registered via Firebase',
                'user'    => [
                    'id'         => $phone->id,
                    'name'       => $phone->name,
                    'email'      => $phone->email,
                    'auth_token' => $phone->auth_token,
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Invalid Firebase ID Token',
                'exception' => $e->getMessage(),
            ], 401);
        }
    }
}

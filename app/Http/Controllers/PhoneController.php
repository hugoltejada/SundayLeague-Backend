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
            'device_id'          => $data['device_id'] ?? null,
            'platform'           => $data['platform'] ?? null,
            'notification_token' => $data['notification_token'] ?? null,
            'auth'               => false,
            'auth_code'          => str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
        ]);

        // enviar email con código
        Mail::to($phone->email)->send(new PhoneConfirmationCodeMail($phone));

        return response()->json([
            'error'   => false,
            'message' => 'Verification code sent',
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
     * Verificación con Google ID Token
     */
    public function verifyGoogle(PhoneVerifyGoogleRequest $request)
    {
        $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($request->id_token);

        if (!$payload) {
            return response()->json(['error' => true, 'message' => 'Invalid Google token'], 401);
        }

        $googleId = $payload['sub'];   // ID único de Google
        $email    = $payload['email'] ?? null;
        $name     = $payload['name'] ?? 'Usuario';

        $phone = Phone::updateOrCreate(
            ['google_id' => $googleId],
            [
                'name'               => $name,
                'email'              => $email,
                'device_id'          => $request->device_id,
                'platform'           => $request->platform,
                'notification_token' => $request->notification_token,
                'auth'               => true,
                'authorized_at'      => now(),
            ]
        );

        return response()->json([
            'error' => false,
            'data'  => $phone,
        ], 200);
    }
}

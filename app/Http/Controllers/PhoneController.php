<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    public function registryEmail(Request $request)
    {
        try {
            $firebaseUid   = $request->attributes->get('firebase_id');
            $firebaseEmail = $request->attributes->get('firebase_email');

            if (!$firebaseEmail) {
                return response()->json(['error' => true, 'message' => 'Firebase token missing email'], 422);
            }

            $name              = $request->input('name') ?? 'User';
            $platform          = $request->input('platform') ?? '';
            $notificationToken = $request->input('notification_token') ?? '';

            $phone = Phone::firstOrCreate(
                ['firebase_id' => $firebaseUid],
                [
                    'name'               => $name,
                    'email'              => $firebaseEmail,
                    'platform'           => $platform,
                    'notification_token' => $notificationToken,
                    'auth'               => true,
                    'authorized_at'      => now(),
                    'auth_token'         => Str::random(60),
                ]
            );

            if (!$phone->player) {
                $phone->player()->create([
                    'name' => $phone->name,
                    'avatar' => $request->input('avatar'),
                ]);
            }
            if (!$phone->supporter) {
                $phone->supporter()->create(['nickname' => $phone->name]);
            }

            return response()->json([
                'error'   => false,
                'message' => 'Bootstrap ok',
                'phone'   => $phone,
            ]);
        } catch (\Throwable $e) {
            \Log::error('[Controller] Error registryEmail: ' . $e->getMessage());
            return response()->json([
                'error'     => true,
                'message'   => 'Invalid Firebase ID Token',
                'exception' => $e->getMessage(),
            ], 401);
        }
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
                    'firebase_id'          => $googleId,
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
                    'avatar' => $request->input('avatar'),
                ]);

                $phone->supporter()->create([
                    'nickname' => $phone->name,
                ]);
            } else {
                $phone->restore();
                $phone->update([
                    'name'               => $name,
                    'firebase_id'          => $googleId,
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

    /**
     * Inicio de sesión con email y contraseña
     */
    public function loginEmail(Request $request)
    {
        try {
            $firebaseUid   = $request->attributes->get('firebase_id');
            $firebaseEmail = $request->attributes->get('firebase_email');

            if (!$firebaseEmail) {
                return response()->json(['error' => true, 'message' => 'Firebase token missing email'], 422);
            }

            $platform          = $request->input('platform') ?? '';
            $notificationToken = $request->input('notification_token') ?? '';

            // Buscar el Phone
            $phone = Phone::where('firebase_id', $firebaseUid)->first();

            if (!$phone) {
                return response()->json(['error' => true, 'message' => 'Phone not found'], 404);
            }

            // Actualizar datos de sesión
            $phone->update([
                'platform'           => $platform,
                'notification_token' => $notificationToken,
                'auth'               => true,
                'authorized_at'      => now(),
                'auth_token'         => Str::random(60), // 🔑 refrescamos auth_token
            ]);

            // Aseguramos que exista player y obtenemos avatar
            $phone->load('player');
            $avatar = $phone->player->avatar ?? null;

            return response()->json([
                'error'   => false,
                'message' => 'Login ok',
                'phone'   => $phone,
                'avatar'  => $avatar,
            ]);
        } catch (\Throwable $e) {
            \Log::error('[Controller] Error loginEmail: ' . $e->getMessage());
            return response()->json([
                'error'     => true,
                'message'   => 'Invalid Firebase ID Token',
                'exception' => $e->getMessage(),
            ], 401);
        }
    }
}

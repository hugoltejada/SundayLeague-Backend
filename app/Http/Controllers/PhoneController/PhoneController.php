<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PhoneRegisterRequest;
use App\Http\Requests\PhoneCheckRequest;
use App\Models\Phone;
use App\Models\Player;
use Illuminate\Support\Facades\Mail;
use App\Mail\PhoneConfirmationCodeMail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PhoneController extends Controller
{
    public function register(PhoneRegisterRequest $request)
    {
        $data = $request->validated();

        // --- Crear o actualizar player ---
        $player = Player::create([
            'club_id'    => null, // aún no pertenece a un club
            'name'       => $data['name'],
            'age'        => $data['age'] ?? null,
            'position'   => $data['position'] ?? null,
            'nationality' => $data['nationality'] ?? null,
        ]);

        // --- Crear o actualizar phone por device_id (incluye soft-deleted) ---
        $phone = Phone::withTrashed()->where('device_id', $data['device_id'])->first();

        if (!$phone) {
            $phone = new Phone();
            $phone->device_id = $data['device_id'];
        } else {
            $phone->restore(); // por si estaba soft-deleted
        }

        $phone->player_id          = $player->id;
        $phone->email              = $data['email'] ?? null;
        $phone->platform           = $data['platform'] ?? null;
        $phone->notification_token = $data['notification_token'] ?? null;

        // Generar código de verificación de 4 dígitos
        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $phone->auth         = false;
        $phone->auth_code    = $code;
        $phone->authorized_at = null;
        $phone->save();

        // Enviar email (si hay email)
        if (!empty($phone->email)) {
            Mail::to($phone->email)->send(new PhoneConfirmationCodeMail($phone));
        }

        // Respuesta (en local devolvemos también el código)
        $payload = [
            'error'   => false,
            'message' => 'Verification code sent',
            'data'    => [
                'player' => $player,
                'phone'  => $phone,
            ],
        ];

        if (app()->environment('local')) {
            $payload['dev_code'] = $code;
        }

        return response()->json($payload, 200);
    }

    public function verify(PhoneCheckRequest $request)
    {
        $data = $request->validated();

        $phone = Phone::where('device_id', $data['device_id'])
            ->where('auth_code', $data['auth_code'])
            ->first();

        if (!$phone) {
            return response()->json(['error' => true, 'message' => 'Invalid code or device'], 404);
        }

        if ($phone->auth) {
            return response()->json(['error' => true, 'message' => 'Already verified'], 409);
        }

        $phone->auth = true;
        $phone->authorized_at = Carbon::now();
        $phone->save();

        return response()->json([
            'error'   => false,
            'message' => 'Verified',
            'phone_id' => $phone->id,
        ], 200);
    }
}

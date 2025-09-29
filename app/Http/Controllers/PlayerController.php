<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;

class PlayerController extends Controller
{
    /**
     * Actualiza el avatar (string key) del jugador autenticado.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|string|max:100',
        ]);

        $phone = $request->get('phone'); // inyectado por middleware auth.phone
        if (!$phone || !$phone->player) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        /** @var Player $player */
        $player = $phone->player;
        $player->avatar = $request->input('avatar');
        $player->save();

        return response()->json([
            'message' => 'Avatar actualizado',
            'player' => $player,
        ]);
    }
}

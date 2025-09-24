<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\StoreClubRequest;

class ClubController extends Controller
{
    /**
     * Genera un código único de invitación
     */
    private function generateUniqueInvitationCode(): string
    {
        do {
            // genera 12 caracteres alfanuméricos en mayúsculas
            $random = strtoupper(Str::random(12));
            $code = 'CLUB-' . $random;
        } while (Club::where('invitation_code', $code)->exists());

        return $code;
    }


    /**
     * Crear un nuevo club
     */
    public function store(StoreClubRequest $request)
    {
        $phone = $request->get('phone'); // inyectado por middleware
        $player = $phone->player;

        $data = $request->validated();
        $data['invitation_code'] = $this->generateUniqueInvitationCode();

        if ($phone) {

            if ($phone->player) {
                $data['president_id'] = $phone->player->id;
            } else {
            }
        } else {
        }

        $club = Club::create($data);

        // añadir también al pivot club_player
        $club->players()->attach($player->id, [
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Club creado con éxito',
            'club'    => $club,
        ], 201);
    }


    /**
     * Devuelve los clubs asociados al phone (como player o supporter).
     */
    public function myClubs(Request $request)
    {
        $phone = $request->get('phone'); // inyectado por el middleware

        $clubsAsPlayer = $phone->player
            ? $phone->player->clubs()->with('president')->get()
            : collect();

        $clubsAsSupporter = $phone->supporter
            ? $phone->supporter->clubs()->with('president')->get()
            : collect();

        return response()->json([
            'as_player'    => $clubsAsPlayer,
            'as_supporter' => $clubsAsSupporter,
        ]);
    }
}

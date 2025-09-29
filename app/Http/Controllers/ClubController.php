<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\StoreClubRequest;
use App\Http\Resources\ClubResource;

class ClubController extends Controller
{
    /**
     * Genera un código único de invitación
     */
    private function generateUniqueInvitationCode(): string
    {
        do {
            // genera 12 caracteres alfanuméricos en mayúsculas
            $random = strtoupper(Str::random(8));
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

        if ($phone && $phone->player) {
            $data['president_id'] = $phone->player->id;
        }

        // Guardar club con name, description e image_url
        $club = Club::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'image_url'   => $data['image_url'] ?? null, // 👈 URL de Firebase
            'invitation_code' => $data['invitation_code'],
            'president_id'    => $data['president_id'] ?? null,
        ]);

        // añadir también al pivot club_player
        if ($player) {
            $club->players()->attach($player->id, [
                'is_active' => true,
            ]);
        }

        return response()->json([
            'message' => 'Club creado con éxito',
            'club'    => $club,
        ], 201);
    }


    /**
     * Devuelve los clubs asociados al phone (como player o supporter).
     * Incluye presidente y todos los jugadores del club.
     */
    public function myClubs(Request $request)
    {
        $phone = $request->get('phone'); // inyectado por el middleware

        // Clubs como jugador (solo si está aprobado)
        $clubsAsPlayer = $phone->player
            ? $phone->player->clubs()
            ->wherePivot('is_active', true) // 👈 solo jugadores aprobados
            ->with([
                'president',
                'players' => function ($query) {
                    $query->with('phone'); // 👈 opcional, para traer también datos del phone
                },
            ])
            ->get()
            : collect();

        // Clubs como supporter
        $clubsAsSupporter = $phone->supporter
            ? $phone->supporter->clubs()
            ->with([
                'president',
                'players' => function ($query) {
                    $query->with('phone');
                },
            ])
            ->get()
            : collect();

        return response()->json([
            'as_player'    => ClubResource::collection($clubsAsPlayer),
            'as_supporter' => ClubResource::collection($clubsAsSupporter),
        ]);
    }

    /**
     * Actualiza el horario del club
     */
    public function updateSchedule(Request $request, Club $club)
    {
        $validated = $request->validate([
            'default_schedules' => 'required|array',
            'default_schedules.*.dayId' => 'required|string',
            'default_schedules.*.startTime' => 'required|integer|min:0|max:1439',
            'match_duration' => 'required|integer|min:15|max:240',
        ]);

        $club->update([
            'default_schedules' => $validated['default_schedules'],
            'match_duration' => $validated['match_duration'],
        ]);

        return response()->json([
            'message' => 'Fechas actualizadas correctamente',
            'club' => $club,
        ]);
    }

    /**
     * Solicitar unirse a un club
     */
    public function requestJoin(Request $request)
    {
        $request->validate([
            'invitation_code' => 'required|string',
        ]);

        $phone = $request->get('phone');
        $player = $phone->player;

        if (!$player) {
            return response()->json(['message' => 'Solo los jugadores pueden unirse a un club'], 403);
        }

        $club = Club::where('invitation_code', $request->invitation_code)->first();
        if (!$club) {
            return response()->json(['message' => 'Código de invitación inválido'], 404);
        }

        // Si ya tiene una relación, impedir duplicados
        $exists = $club->players()->where('player_id', $player->id)->first();
        if ($exists) {
            return response()->json(['message' => 'Ya existe una solicitud o eres miembro del club'], 422);
        }

        // Crear solicitud (queda pendiente hasta aprobación del presidente)
        $club->players()->attach($player->id, [
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Solicitud enviada, pendiente de aprobación del presidente',
            'club'    => $club,
        ], 201);
    }

    /**
     * Aprueba la solicitud de un jugador para unirse al club
     */
    public function approveJoin(Request $request, Club $club, $playerId)
    {
        $phone = $request->get('phone');
        $president = $phone->player;

        if ($club->president_id !== $president->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $club->players()->updateExistingPivot($playerId, [
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Jugador aprobado correctamente']);
    }
}

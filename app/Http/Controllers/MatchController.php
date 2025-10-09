<?php

namespace App\Http\Controllers;

use App\Models\MatchGuest;
use App\Models\MatchPlayer;
use App\Models\Matches;
use App\Models\Season;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MatchController extends Controller
{
    public function store(Request $request)
    {
        $phone = $request->attributes->get('phone');
        $player = $phone?->player;

        if (!$phone || !$player) {
            return response()->json([
                'error' => true,
                'message' => 'No se encontró un jugador asociado al usuario.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'club_id' => ['required', Rule::exists('clubs', 'id')],
            'season_id' => ['nullable', Rule::exists('seasons', 'id')],
            'match_date' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*.team_side' => ['required', Rule::in(['home', 'away'])],
            'participants.*.player_id' => ['nullable', Rule::exists('players', 'id')],
            'participants.*.guest_name' => ['nullable', 'string', 'max:255'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $participants = $request->input('participants', []);

            foreach ($participants as $index => $participant) {
                $hasPlayer = !empty($participant['player_id']);
                $hasGuest = !empty($participant['guest_name']);

                if (!$hasPlayer && !$hasGuest) {
                    $validator->errors()->add(
                        "participants.$index",
                        'Cada participante debe incluir un jugador válido o un nombre de invitado.'
                    );
                }

                if ($hasPlayer && $hasGuest) {
                    $validator->errors()->add(
                        "participants.$index",
                        'No se puede enviar un jugador y un invitado al mismo tiempo.'
                    );
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Datos inválidos.',
                'details' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        if (!empty($data['season_id'])) {
            $seasonBelongsToClub = Season::where('id', $data['season_id'])
                ->where('club_id', $data['club_id'])
                ->exists();

            if (!$seasonBelongsToClub) {
                return response()->json([
                    'error' => true,
                    'message' => 'La temporada seleccionada no pertenece al club indicado.',
                ], 422);
            }
        }

        $clubPlayerIds = DB::table('club_player')
            ->where('club_id', $data['club_id'])
            ->pluck('player_id')
            ->all();

        $invalidPlayers = collect($data['participants'])
            ->filter(fn($participant) => !empty($participant['player_id']) && !in_array($participant['player_id'], $clubPlayerIds, true))
            ->pluck('player_id')
            ->values();

        if ($invalidPlayers->isNotEmpty()) {
            return response()->json([
                'error' => true,
                'message' => 'Algunos jugadores no pertenecen al club indicado.',
                'invalid_player_ids' => $invalidPlayers,
            ], 422);
        }

        $matchDate = Carbon::parse($data['match_date']);

        $match = DB::transaction(function () use ($data, $player, $matchDate) {
            $match = Matches::create([
                'club_id' => $data['club_id'],
                'season_id' => $data['season_id'] ?? null,
                'created_by' => $player->id,
                'match_date' => $matchDate,
                'location' => $data['location'] ?? null,
            ]);

            foreach ($data['participants'] as $participant) {
                if (!empty($participant['player_id'])) {
                    MatchPlayer::create([
                        'matches_id' => $match->id,
                        'player_id' => $participant['player_id'],
                        'team_side' => $participant['team_side'],
                    ]);
                } elseif (!empty($participant['guest_name'])) {
                    MatchGuest::create([
                        'matches_id' => $match->id,
                        'name' => $participant['guest_name'],
                        'team_side' => $participant['team_side'],
                    ]);
                }
            }

            return $match;
        });

        return response()->json([
            'error' => false,
            'message' => 'Partido creado correctamente.',
            'data' => [
                'match' => $match->load(['players', 'guests']),
            ],
        ], 201);
    }
}

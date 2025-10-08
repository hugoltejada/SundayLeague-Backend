<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Club; // asumiendo existe
use App\Models\Season; // modelo Season
use Illuminate\Support\Facades\DB;

class SeasonController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'club_id' => 'required|integer|exists:clubs,id',
            'start_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                '_ok' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        // Modelo Season mínimo; si no existe deberíamos crearlo con migración.
        if (!class_exists(Season::class)) {
            // Retorno placeholder hasta que se cree modelo y migración reales.
            return response()->json([
                '_ok' => true,
                'season' => [
                    'id' => 0,
                    'club_id' => $data['club_id'],
                    'start_date' => $data['start_date'],
                    'mode' => $data['mode'] ?? 'fresh'
                ],
                'warning' => 'Season model/migration not yet implemented'
            ]);
        }

        try {
            $season = DB::transaction(function () use ($data) {
                // Calcular siguiente season_number dentro del club
                $nextNumber = (int) (Season::where('club_id', $data['club_id'])->max('season_number') ?? 0) + 1;

                // Desmarcar temporadas actuales anteriores
                Season::where('club_id', $data['club_id'])->where('is_current', true)->update(['is_current' => false]);

                return Season::create([
                    'club_id' => $data['club_id'],
                    'season_number' => $nextNumber,
                    'start_date' => $data['start_date'],
                    'is_current' => true,
                ]);
            });

            return response()->json([
                '_ok' => true,
                'season' => $season,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                '_ok' => false,
                'message' => 'No se pudo crear la temporada',
                'exception' => $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use App\Models\Phone;
use App\Models\Player;
use App\Models\Supporter;
use App\Models\Club;

class BasicSeeder extends Seeder
{
    /**
     * Genera un código único de invitación tipo CLUB-XXXXXXXX.
     */
    private function generateUniqueInvitationCode(): string
    {
        do {
            $random = strtoupper(Str::random(8));
            $code = 'CLUB-' . $random;
        } while (Club::where('invitation_code', $code)->exists());

        return $code;
    }

    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // ⚠️ Opcional: limpiar tablas (solo en entornos de desarrollo)
        // DB::statement('SET FOREIGN_KEY_CHECKS=0');
        // DB::table('match_player')->truncate();
        // DB::table('matches')->truncate();
        // DB::table('club_supporter')->truncate();
        // DB::table('club_player')->truncate();
        // DB::table('clubs')->truncate();
        // DB::table('supporters')->truncate();
        // DB::table('players')->truncate();
        // DB::table('phones')->truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ============================================================
        // 1) Crear 18 usuarios aleatorios (cada uno con player+supporter)
        // ============================================================
        $randomPlayers = [];

        $avatarPool = [
            'personaje1',
            'personaje2',
            'personaje3',
            'personaje4',
            'personaje5',
            'personaje6',
            'personaje7',
            'personaje8',
            'personaje9',
            'personaje10',
            'personaje11',
            'personaje12',
            'personaje13'
        ];

        for ($i = 1; $i <= 18; $i++) {
            // Emails únicos garantizados por faker
            $email = $faker->unique()->safeEmail();

            // Si ya existiese por soft delete, restauramos
            $phone = Phone::withTrashed()->firstOrCreate(
                ['email' => $email],
                [
                    'name'               => $faker->name(),
                    'platform'           => 'ANDROID',
                    'notification_token' => null,
                    'password'           => Hash::make('password'),
                    'firebase_id'        => Str::random(28),
                    'auth'               => true,
                    'authorized_at'      => now(),
                    'auth_token'         => Str::random(60),
                ]
            );
            if (method_exists($phone, 'trashed') && $phone->trashed()) {
                $phone->restore();
            }

            // Player
            $player = Player::firstOrCreate(
                ['phone_id' => $phone->id],
                [
                    'name'        => $phone->name,
                    'nationality' => 'ES',
                    'avatar'      => $faker->randomElement($avatarPool),
                ]
            );

            // Supporter
            Supporter::firstOrCreate(
                ['phone_id' => $phone->id],
                ['nickname' => $phone->name]
            );

            $randomPlayers[] = $player;
        }

        // ============================================================
        // 2) Usuario PRESIDENTE: Hugol2014 (president de ambos clubs)
        // ============================================================
        $presidentPhone = Phone::withTrashed()->firstOrCreate(
            ['email' => 'Hugol2014@hotmail.com'],
            [
                'name'               => 'Hugol2014',
                'platform'           => 'ANDROID',
                'notification_token' => null,
                'password'           => Hash::make('123456'),
                'firebase_id'        => 'SI8GWpO5lpP27TllCiV8rvteGDh2',
                'auth'               => true,
                'authorized_at'      => now(),
                'auth_token'         => Str::random(60),
            ]
        );
        if (method_exists($presidentPhone, 'trashed') && $presidentPhone->trashed()) {
            $presidentPhone->restore();
        }

        $presidentPlayer = Player::updateOrCreate(
            ['phone_id' => $presidentPhone->id],
            [
                'name' => 'Hugol2014',
                'avatar' => 'personaje1'
            ]
        );
        Supporter::firstOrCreate(
            ['phone_id' => $presidentPhone->id],
            ['nickname' => 'Hugol2014']
        );

        // ============================================================
        // 3) Usuario JUGADOR: Jugador (miembro de ambos clubs)
        // ============================================================
        $jugadorPhone = Phone::withTrashed()->firstOrCreate(
            ['email' => 'Jugador@gmail.com'],
            [
                'name'               => 'Jugador',
                'platform'           => 'ANDROID',
                'notification_token' => null,
                'password'           => Hash::make('123456'),
                'firebase_id'        => 'FeGHkoB8DAVRj1HATb1730pGWTc2',
                'auth'               => true,
                'authorized_at'      => now(),
                'auth_token'         => Str::random(60),
            ]
        );
        if (method_exists($jugadorPhone, 'trashed') && $jugadorPhone->trashed()) {
            $jugadorPhone->restore();
        }

        $jugadorPlayer = Player::updateOrCreate(
            ['phone_id' => $jugadorPhone->id],
            [
                'name' => 'Jugador',
                'avatar' => 'personaje2'
            ]
        );
        Supporter::firstOrCreate(
            ['phone_id' => $jugadorPhone->id],
            ['nickname' => 'Jugador']
        );

        // ============================================================
        // 4) Crear los 2 clubs con presidente Hugol2014
        // ============================================================
        $defaultSchedules = [
            ['dayId' => 'monday', 'startTime' => 1260],
        ];

        // Club 1
        $club1 = Club::firstOrCreate(
            ['name' => 'Compadres Viejos'],
            [
                'description'      => 'club principal de pruebas',
                'image_url'        => null,
                'invitation_code'  => $this->generateUniqueInvitationCode(),
                'default_schedules' => $defaultSchedules, // columna JSON
                'match_duration'   => 60,
            ]
        );
        // Si ya existía, asegúrate de que tiene estos valores actualizados
        $club1->fill([
            'description'       => 'club principal de pruebas',
            'default_schedules' => $defaultSchedules,
            'match_duration'    => 60,
        ]);
        $club1->president_id = $presidentPlayer->id;
        $club1->save();

        // Club 2
        $club2 = Club::firstOrCreate(
            ['name' => 'Peña Galácticos'],
            [
                'description'      => 'club secundario pruebas',
                'image_url'        => null,
                'invitation_code'  => $this->generateUniqueInvitationCode(),
                'default_schedules' => $defaultSchedules,
                'match_duration'   => 60,
            ]
        );
        $club2->fill([
            'description'       => 'club secundario pruebas',
            'default_schedules' => $defaultSchedules,
            'match_duration'    => 60,
        ]);
        $club2->president_id = $presidentPlayer->id;
        $club2->save();

        // ============================================================
        // 5) Vinculaciones (pivot club_player)
        //    - Presidente activo en ambos
        //    - Jugador activo en ambos
        //    - 18 aleatorios repartidos (mitad activos, mitad pendientes)
        // ============================================================
        // Presidente
        $club1->players()->syncWithoutDetaching([$presidentPlayer->id => ['is_active' => true]]);
        $club2->players()->syncWithoutDetaching([$presidentPlayer->id => ['is_active' => true]]);

        // Jugador
        $club1->players()->syncWithoutDetaching([$jugadorPlayer->id => ['is_active' => true]]);
        $club2->players()->syncWithoutDetaching([$jugadorPlayer->id => ['is_active' => true]]);

        // Repartir 18 random: 10 al club1 y 8 al club2
        $randomForClub1 = collect($randomPlayers)->shuffle()->take(10);
        $randomForClub2 = collect($randomPlayers)->diff($randomForClub1)->take(8);

        // En cada club: la mitad activos y la mitad pendientes (is_active=false)
        foreach ($randomForClub1->values() as $idx => $p) {
            $club1->players()->syncWithoutDetaching([
                $p->id => ['is_active' => $idx % 2 === 0], // pares activos, impares pendientes
            ]);
        }
        foreach ($randomForClub2->values() as $idx => $p) {
            $club2->players()->syncWithoutDetaching([
                $p->id => ['is_active' => $idx % 2 === 1], // invertimos para variar
            ]);
        }

        $this->command->info('✅ BasicSeeder: 20 usuarios creados (18 random + 2 específicos), 2 clubs, memberships creadas.');
        $this->command->info('   Presidente: Hugol2014; Jugador: Jugador; Clubs: Compadres Viejos / Peña Galácticos');
    }
}

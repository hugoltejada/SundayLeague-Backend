<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Config;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            [
                'key'   => 'app_name',
                'value' => 'SundayLeague',
            ],
            [
                'key'   => 'mail_from_address',
                'value' => 'noreply@sundayleague.com',
            ],
            [
                'key'   => 'mail_from_name',
                'value' => 'SundayLeague App',
            ],
        ];

        foreach ($configs as $config) {
            Config::updateOrCreate(['key' => $config['key']], ['value' => $config['value']]);
        }
    }
}

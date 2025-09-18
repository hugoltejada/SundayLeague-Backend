<?php

use App\Models\Config;

if (! function_exists('app_config')) {
    function app_config($key, $default = null)
    {
        return optional(Config::where('key', $key)->first())->value ?? $default;
    }
}

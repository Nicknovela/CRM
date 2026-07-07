<?php

return [
    'name'     => env('APP_NAME', 'CRM'),
    'url'      => env('APP_URL', ''),
    'timezone' => env('APP_TIMEZONE', 'America/La_Paz'),
    'debug'    => env('APP_DEBUG', 'false') === 'true',
    'session_lifetime' => 7200, // 2 horas
];

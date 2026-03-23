<?php

return [
    'name'     => 'CRM',
    'url'      => getenv('APP_URL') ?: 'http://localhost',
    'timezone' => getenv('APP_TIMEZONE') ?: 'America/La_Paz',
    'debug'    => getenv('APP_DEBUG') === 'true',
    'session_lifetime' => 7200, // 2 hours
];

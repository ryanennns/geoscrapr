<?php

return [
    'device_token'                    => env('GEO_DEVICE_TOKEN'),
    'cfuvid'                          => env('GEO_CFUVID'),
    'ncfa'                            => env('GEO_NCFA'),
    'session'                         => env('GEO_SESSION'),
    'minimum_average_country_players' => env('GEO_MIN_AVERAGE_COUNTRY_PLAYERS', 500),
];

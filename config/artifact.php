<?php

return [
    'model' => LaravelJutsu\Artifact\Artifact::class,
    'table_name' => 'artifacts',
    'routes' => [
        'prefix' => env('ARTIFACT_ROUTE_PREFIX', 'artifacts'),
        'middleware' => ['web'],
    ],
    'signed_url' => [
        'expiration_minutes' => env('ARTIFACT_SIGNED_URL_EXPIRATION', 60),
    ],
];
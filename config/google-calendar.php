<?php

return [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
    'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),
    'service_account_credentials_json' => storage_path('app/google-calendar/credentials.json'),
];



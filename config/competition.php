<?php

return [
    'max_teams' => 15,
    'registration_starts_at' => env('COMPETITION_REGISTRATION_STARTS_AT', '2026-10-05 00:00:00'),
    'registration_ends_at' => env('COMPETITION_REGISTRATION_ENDS_AT', '2026-10-15 23:59:00'),
    'contact_email' => env('COMPETITION_CONTACT_EMAIL', 'comissaopontedepalito@ifspcaraguatatuba.edu.br'),
    'admin_emails' => array_filter(array_map('trim', explode(',', env('COMPETITION_ADMIN_EMAILS', '')))),
    'bootstrap_admin' => [
        'email' => env('BOOTSTRAP_ADMIN_EMAIL'),
        'name' => env('BOOTSTRAP_ADMIN_NAME', 'Comissão organizadora'),
        'password' => env('BOOTSTRAP_ADMIN_PASSWORD'),
    ],
];

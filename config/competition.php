<?php

return [
    'max_teams' => 15,
    'civil_engineering_quota' => 10,
    'registration_starts_at' => env('COMPETITION_REGISTRATION_STARTS_AT'),
    'registration_ends_at' => env('COMPETITION_REGISTRATION_ENDS_AT'),
    'civil_engineering_course' => 'Engenharia Civil',
    'contact_email' => env('COMPETITION_CONTACT_EMAIL'),
    'admin_emails' => array_filter(array_map('trim', explode(',', env('COMPETITION_ADMIN_EMAILS', '')))),
];

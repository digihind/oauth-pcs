<?php

return [
    'access_token_ttl' => env('SSO_ACCESS_TOKEN_TTL', 30),
    'refresh_token_ttl' => env('SSO_REFRESH_TOKEN_TTL', 30),
    'signing_key' => env('SSO_SIGNING_KEY', 'change-me'),
    'algorithm' => env('SSO_SIGNING_ALGORITHM', 'HS256'),
];

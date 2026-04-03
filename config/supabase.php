<?php

return [
    'url' => env('SUPABASE_URL'),
    'anon_key' => env('SUPABASE_ANON_KEY'),
    'service_role_key' => env('SUPABASE_SERVICE_ROLE_KEY'),
    'enforce_admin_provisioning' => env('SUPABASE_ENFORCE_ADMIN_PROVISIONING', false),
    'jwt_issuer' => env('SUPABASE_JWT_ISSUER'),
    'jwks_url' => env('SUPABASE_JWKS_URL'),
    'auth_user_endpoint' => env('SUPABASE_AUTH_USER_ENDPOINT', '/auth/v1/user'),
];

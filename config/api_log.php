<?php

return [

    'max_body_bytes' => (int) env('API_LOG_MAX_BODY_BYTES', 65536),

    'redacted_headers' => ['authorization', 'proxy-authorization', 'cookie', 'set-cookie', 'x-api-key'],

    'redacted_fields' => ['password', 'password_confirmation', 'token', 'access_token', 'refresh_token', 'secret', 'api_key'],

];

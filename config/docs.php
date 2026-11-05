<?php

declare(strict_types=1);

return [
    'username' => env('DOCS_USERNAME', ''),
    'password' => env('DOCS_PASSWORD', ''),
    'spec_path' => base_path('docs/openapi.yaml'),
    'readme_path' => base_path('README.md'),
];

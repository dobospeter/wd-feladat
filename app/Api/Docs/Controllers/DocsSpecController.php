<?php

declare(strict_types=1);

namespace App\Api\Docs\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DocsSpecController
{
    public function __invoke(): BinaryFileResponse
    {
        return response()->file(config()->string('docs.spec_path'), [
            'Content-Type' => 'application/yaml; charset=UTF-8',
        ]);
    }
}

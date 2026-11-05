<?php

declare(strict_types=1);

namespace Inventory\Docs\Presentation\Http\Controller;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DocsController
{
    public function ui(): Response
    {
        $page = (string) file_get_contents(__DIR__ . '/../../Resources/swagger-ui.html');

        return response(
            str_replace('{{ spec_url }}', e(route('docs.spec', absolute: false)), $page),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=UTF-8'],
        );
    }

    public function spec(): BinaryFileResponse
    {
        return response()->file((string) config('docs.spec_path'), [
            'Content-Type' => 'application/yaml; charset=UTF-8',
        ]);
    }
}

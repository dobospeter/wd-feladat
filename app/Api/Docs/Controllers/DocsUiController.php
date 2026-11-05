<?php

declare(strict_types=1);

namespace App\Api\Docs\Controllers;

use Illuminate\Http\Response;

final class DocsUiController
{
    public function __invoke(): Response
    {
        $page = (string) file_get_contents(__DIR__ . '/../Views/swagger-ui.html');

        return response(
            str_replace('{{ spec_url }}', e(route('docs.spec', absolute: false)), $page),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=UTF-8'],
        );
    }
}

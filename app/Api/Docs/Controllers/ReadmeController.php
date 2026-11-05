<?php

declare(strict_types=1);

namespace App\Api\Docs\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;

final class ReadmeController
{
    public function __invoke(): Response
    {
        $markdown = (string) file_get_contents(config()->string('docs.readme_path'));

        // Headings get GitHub-style ids so the README's internal links work on this page too.
        $content = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'id_prefix' => '',
                'fragment_prefix' => '',
                'apply_id_to_heading' => true,
                'insert' => 'none',
            ],
        ], [new HeadingPermalinkExtension()]);

        $page = (string) file_get_contents(__DIR__ . '/../Views/readme.html');

        return response(
            str_replace('{{ content }}', $content, $page),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=UTF-8'],
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Routing\Route;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\Route as RouteFacade;

class OpenApiSpecTest extends TestCase
{
    public function testTheDocumentedOperationsMatchTheRegisteredApiRoutes(): void
    {
        $spec = Yaml::parseFile(base_path('docs/openapi.yaml'));

        $documented = [];
        foreach ($spec['paths'] as $path => $item) {
            foreach (array_intersect(['get', 'post', 'put', 'patch', 'delete'], array_keys($item)) as $method) {
                $documented[] = strtoupper($method) . ' ' . $this->normalise('/api/v1' . $path);
            }
        }

        $registered = [];
        foreach (RouteFacade::getRoutes()->getRoutes() as $route) {
            /** @var Route $route */
            if (! str_starts_with($route->uri(), 'api/')) {
                continue;
            }

            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                $registered[] = $method . ' ' . $this->normalise('/' . $route->uri());
            }
        }

        sort($documented);
        sort($registered);

        $this->assertSame($registered, $documented);
    }

    public function testEveryOperationHasAUniqueOperationId(): void
    {
        $spec = Yaml::parseFile(base_path('docs/openapi.yaml'));

        $ids = [];
        foreach ($spec['paths'] as $item) {
            foreach (['get', 'post', 'put', 'patch', 'delete'] as $method) {
                if (isset($item[$method])) {
                    $ids[] = $item[$method]['operationId'];
                }
            }
        }

        $this->assertSame($ids, array_values(array_unique($ids)));
    }

    private function normalise(string $uri): string
    {
        return preg_replace('/\{[^}]+\}/', '{}', $uri);
    }
}

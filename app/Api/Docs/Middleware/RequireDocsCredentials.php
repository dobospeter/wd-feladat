<?php

declare(strict_types=1);

namespace App\Api\Docs\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireDocsCredentials
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = config()->string('docs.username');
        $password = config()->string('docs.password');

        if ($username === '' || $password === '' || ! $this->matches($request, $username, $password)) {
            return response('Authentication required.', Response::HTTP_UNAUTHORIZED, [
                'WWW-Authenticate' => 'Basic realm="Asset Register API documentation", charset="UTF-8"',
            ]);
        }

        return $next($request);
    }

    private function matches(Request $request, string $username, string $password): bool
    {
        // Compare both values unconditionally so timing does not reveal which one was wrong.
        $usernameMatches = hash_equals($username, (string) $request->getUser());
        $passwordMatches = hash_equals($password, (string) $request->getPassword());

        return $usernameMatches && $passwordMatches;
    }
}

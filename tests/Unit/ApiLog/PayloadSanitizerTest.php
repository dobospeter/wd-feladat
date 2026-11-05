<?php

declare(strict_types=1);

namespace Tests\Unit\ApiLog;

use PHPUnit\Framework\TestCase;
use App\Modules\ApiLog\Support\PayloadSanitizer;

class PayloadSanitizerTest extends TestCase
{
    private function sanitizer(int $maxBytes = 1000): PayloadSanitizer
    {
        return new PayloadSanitizer($maxBytes, ['Authorization'], ['password', 'token']);
    }

    public function testSensitiveHeadersAreRedactedAndNamesLowercased(): void
    {
        $headers = $this->sanitizer()->headers(['Authorization' => ['Bearer x'], 'Accept' => ['application/json']]);

        $this->assertSame(['authorization' => '[REDACTED]', 'accept' => ['application/json']], $headers);
    }

    public function testJsonFieldsAreRedactedAtAnyDepth(): void
    {
        $json = '{"user":{"Password":"p","name":"n"},"items":[{"token":"t"}]}';
        $body = $this->sanitizer()->body($json, 'application/json');

        $this->assertSame('{"user":{"Password":"[REDACTED]","name":"n"},"items":[{"token":"[REDACTED]"}]}', $body);
    }

    public function testMalformedJsonIsKeptAsIs(): void
    {
        $this->assertSame('{"name": ', $this->sanitizer()->body('{"name": ', 'application/json'));
    }

    public function testFormBodiesAreRedacted(): void
    {
        $body = $this->sanitizer()->body('name=a&password=secret', 'application/x-www-form-urlencoded');

        $this->assertSame('{"name":"a","password":"[REDACTED]"}', $body);
    }

    public function testEmptyBodiesBecomeNull(): void
    {
        $this->assertNull($this->sanitizer()->body('', 'application/json'));
        $this->assertNull($this->sanitizer()->body(null, null));
    }

    public function testBinaryBodiesAreNotStored(): void
    {
        $this->assertSame(
            PayloadSanitizer::BINARY_PLACEHOLDER,
            $this->sanitizer()->body("\x89PNG", 'image/png'),
        );
    }

    public function testLongBodiesAreTruncatedWithoutBreakingMultibyteCharacters(): void
    {
        $body = $this->sanitizer(10)->body(str_repeat('é', 20), 'text/plain');

        $this->assertSame(str_repeat('é', 5) . PayloadSanitizer::TRUNCATION_MARKER, $body);
        $this->assertTrue(mb_check_encoding($body, 'UTF-8'));
    }
}

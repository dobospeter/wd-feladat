<?php

namespace Tests\Unit\ApiLog;

use Inventory\ApiLog\Application\PayloadSanitizer;
use PHPUnit\Framework\TestCase;

class PayloadSanitizerTest extends TestCase
{
    private function sanitizer(int $maxBytes = 1000): PayloadSanitizer
    {
        return new PayloadSanitizer($maxBytes, ['Authorization'], ['password', 'token']);
    }

    public function test_sensitive_headers_are_redacted_and_names_lowercased(): void
    {
        $headers = $this->sanitizer()->headers(['Authorization' => ['Bearer x'], 'Accept' => ['application/json']]);

        $this->assertSame(['authorization' => '[REDACTED]', 'accept' => ['application/json']], $headers);
    }

    public function test_json_fields_are_redacted_at_any_depth(): void
    {
        $body = $this->sanitizer()->body('{"user":{"Password":"p","name":"n"},"items":[{"token":"t"}]}', 'application/json');

        $this->assertSame('{"user":{"Password":"[REDACTED]","name":"n"},"items":[{"token":"[REDACTED]"}]}', $body);
    }

    public function test_malformed_json_is_kept_as_is(): void
    {
        $this->assertSame('{"name": ', $this->sanitizer()->body('{"name": ', 'application/json'));
    }

    public function test_form_bodies_are_redacted(): void
    {
        $body = $this->sanitizer()->body('name=a&password=secret', 'application/x-www-form-urlencoded');

        $this->assertSame('{"name":"a","password":"[REDACTED]"}', $body);
    }

    public function test_empty_bodies_become_null(): void
    {
        $this->assertNull($this->sanitizer()->body('', 'application/json'));
        $this->assertNull($this->sanitizer()->body(null, null));
    }

    public function test_binary_bodies_are_not_stored(): void
    {
        $this->assertSame(
            PayloadSanitizer::BINARY_PLACEHOLDER,
            $this->sanitizer()->body("\x89PNG", 'image/png'),
        );
    }

    public function test_long_bodies_are_truncated_without_breaking_multibyte_characters(): void
    {
        $body = $this->sanitizer(10)->body(str_repeat('é', 20), 'text/plain');

        $this->assertSame(str_repeat('é', 5) . PayloadSanitizer::TRUNCATION_MARKER, $body);
        $this->assertTrue(mb_check_encoding($body, 'UTF-8'));
    }
}

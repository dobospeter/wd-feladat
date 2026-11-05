<?php

declare(strict_types=1);

namespace App\Modules\ApiLog\Support;

use JsonException;
use Illuminate\Container\Attributes\Config;

final readonly class PayloadSanitizer
{
    public const REDACTED = '[REDACTED]';
    public const BINARY_PLACEHOLDER = '[non-text body omitted]';
    public const TRUNCATION_MARKER = '...[truncated]';

    /** @var list<string> */
    private array $redactedHeaders;

    /** @var list<string> */
    private array $redactedFields;

    /**
     * @param  list<string>  $redactedHeaders
     * @param  list<string>  $redactedFields
     */
    public function __construct(
        #[Config('api_log.max_body_bytes')] private int $maxBodyBytes,
        #[Config('api_log.redacted_headers')] array $redactedHeaders,
        #[Config('api_log.redacted_fields')] array $redactedFields,
    ) {
        $this->redactedHeaders = array_map('strtolower', $redactedHeaders);
        $this->redactedFields = array_map('strtolower', $redactedFields);
    }

    /**
     * @param  array<string, mixed>  $headers
     * @return array<string, mixed>
     */
    public function headers(array $headers): array
    {
        $sanitized = [];

        foreach ($headers as $name => $value) {
            $name = strtolower((string) $name);
            $sanitized[$name] = in_array($name, $this->redactedHeaders, true) ? self::REDACTED : $value;
        }

        return $sanitized;
    }

    public function body(?string $content, ?string $contentType): ?string
    {
        if ($content === null || $content === '') {
            return null;
        }

        $contentType = strtolower((string) $contentType);

        if (str_contains($contentType, 'json')) {
            return $this->truncate($this->redactJson($content));
        }

        if (str_contains($contentType, 'x-www-form-urlencoded')) {
            parse_str($content, $fields);

            return $this->truncate($this->encode($this->redactFields($fields)));
        }

        if ($contentType === '' || $this->isTextual($contentType)) {
            return $this->truncate($content);
        }

        return self::BINARY_PLACEHOLDER;
    }

    private function redactJson(string $content): string
    {
        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            // Keep malformed payloads as they are: they are valuable evidence.
            return $content;
        }

        return is_array($decoded) ? $this->encode($this->redactFields($decoded)) : $content;
    }

    /**
     * @param  array<array-key, mixed>  $fields
     * @return array<array-key, mixed>
     */
    private function redactFields(array $fields): array
    {
        foreach ($fields as $key => $value) {
            if (in_array(strtolower((string) $key), $this->redactedFields, true)) {
                $fields[$key] = self::REDACTED;
            } elseif (is_array($value)) {
                $fields[$key] = $this->redactFields($value);
            }
        }

        return $fields;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    private function encode(array $data): string
    {
        return (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    private function isTextual(string $contentType): bool
    {
        return str_starts_with($contentType, 'text/') || str_contains($contentType, 'xml');
    }

    private function truncate(string $payload): string
    {
        if (strlen($payload) <= $this->maxBodyBytes) {
            return $payload;
        }

        return mb_strcut($payload, 0, $this->maxBodyBytes, 'UTF-8') . self::TRUNCATION_MARKER;
    }
}

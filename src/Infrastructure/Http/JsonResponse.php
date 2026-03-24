<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Http;

final class JsonResponse
{
    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws \JsonException
     */
    public static function send(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }
}

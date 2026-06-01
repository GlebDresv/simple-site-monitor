<?php

namespace App\Services\Telegram;

use GuzzleHttp\Exception\ClientException;
use NotificationChannels\Telegram\Exceptions\CouldNotSendNotification;
use Throwable;

class TelegramRateLimitParser
{
    public static function retryAfter(Throwable $exception): ?int
    {
        if ($exception instanceof CouldNotSendNotification) {
            $previous = $exception->getPrevious();

            if ($previous instanceof ClientException) {
                $retryAfter = self::retryAfterFromResponse($previous);

                if ($retryAfter !== null) {
                    return $retryAfter;
                }
            }

            return self::retryAfterFromMessage($exception->getMessage());
        }

        if ($exception instanceof ClientException) {
            return self::retryAfterFromResponse($exception)
                ?? self::retryAfterFromMessage($exception->getMessage());
        }

        return null;
    }

    private static function retryAfterFromResponse(ClientException $exception): ?int
    {
        if (! $exception->hasResponse()) {
            return null;
        }

        $body = (string) $exception->getResponse()->getBody();

        if ($body === '') {
            return null;
        }

        $data = json_decode($body, true);

        if (! is_array($data)) {
            return null;
        }

        $retryAfter = self::normalizeRetryAfter($data['parameters']['retry_after'] ?? null);

        if ($retryAfter !== null) {
            return $retryAfter;
        }

        if (($data['error_code'] ?? null) === 429) {
            return self::retryAfterFromMessage((string) ($data['description'] ?? ''));
        }

        return null;
    }

    private static function retryAfterFromMessage(string $message): ?int
    {
        if (preg_match('/retry after (\d+)/i', $message, $matches) !== 1) {
            return null;
        }

        return self::normalizeRetryAfter($matches[1]);
    }

    private static function normalizeRetryAfter(mixed $value): ?int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            $value = (int) $value;

            return $value > 0 ? $value : null;
        }

        return null;
    }
}

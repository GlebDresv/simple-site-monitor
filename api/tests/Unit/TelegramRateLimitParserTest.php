<?php

namespace Tests\Unit;

use App\Services\Telegram\TelegramRateLimitParser;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use NotificationChannels\Telegram\Exceptions\CouldNotSendNotification;
use PHPUnit\Framework\TestCase;

class TelegramRateLimitParserTest extends TestCase
{
    public function test_reads_retry_after_from_telegram_response_parameters(): void
    {
        $exception = $this->telegramException([
            'ok' => false,
            'error_code' => 429,
            'description' => 'Too Many Requests: retry after 12',
            'parameters' => ['retry_after' => 12],
        ]);

        $this->assertSame(12, TelegramRateLimitParser::retryAfter($exception));
    }

    public function test_falls_back_to_description_when_parameters_are_missing(): void
    {
        $exception = $this->telegramException([
            'ok' => false,
            'error_code' => 429,
            'description' => 'Too Many Requests: retry after 7',
        ]);

        $this->assertSame(7, TelegramRateLimitParser::retryAfter($exception));
    }

    public function test_returns_null_for_non_rate_limit_errors(): void
    {
        $exception = $this->telegramException([
            'ok' => false,
            'error_code' => 400,
            'description' => 'Bad Request: chat not found',
        ]);

        $this->assertNull(TelegramRateLimitParser::retryAfter($exception));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function telegramException(array $payload): CouldNotSendNotification
    {
        $clientException = new ClientException(
            'Client error',
            new Request('POST', 'https://api.telegram.org/bot123/sendMessage'),
            new Response(429, [], (string) json_encode($payload)),
        );

        return CouldNotSendNotification::telegramRespondedWithAnError($clientException);
    }
}

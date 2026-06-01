<?php

namespace App\Services\DomainCheck;

use App\Enums\DomainStatus;
use App\Enums\HttpMethod;
use App\Models\CheckLog;
use App\Models\CheckSetting;
use App\Models\Domain;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class DomainChecker
{
    private const MAX_BODY_LENGTH = 65535;

    public function check(Domain $domain, CheckSetting $checkSetting): CheckLog
    {
        $url = $this->buildUrl($domain->name);
        $redirectsCount = 0;
        $startedAt = microtime(true);

        try {
            $request = Http::withOptions([
                'allow_redirects' => [
                    'max' => 4,
                    'on_redirect' => function () use (&$redirectsCount): void {
                        $redirectsCount++;
                    },
                ],
            ])->timeout($checkSetting->request_timeout);

            $response = match ($checkSetting->method) {
                HttpMethod::Head => $request->head($url),
                HttpMethod::Get => $request->get($url),
            };

            return $domain->checkLogs()->create([
                'checked_at' => now(),
                'response_code' => $response->status(),
                'response_time' => $this->resolveResponseTime($response, $startedAt),
                'response_headers' => $response->headers(),
                'redirects_count' => $redirectsCount,
                'response_body' => $this->extractBody($response, $checkSetting->method),
            ]);
        } catch (ConnectionException $exception) {
            return $domain->checkLogs()->create([
                'checked_at' => now(),
                'response_code' => null,
                'response_time' => $this->resolveElapsedTime($startedAt),
                'response_headers' => null,
                'redirects_count' => $redirectsCount,
                'response_body' => $exception->getMessage(),
            ]);
        }
    }

    public function resolveStatus(?int $responseCode): DomainStatus
    {
        if ($responseCode === null) {
            return DomainStatus::Down;
        }

        return ($responseCode >= 200 && $responseCode < 300)
            ? DomainStatus::Up
            : DomainStatus::Down;
    }

    private function resolveResponseTime(Response $response, float $startedAt): int
    {
        $transferTime = $response->transferStats?->getTransferTime();

        if ($transferTime !== null) {
            return (int) round($transferTime * 1000);
        }

        return $this->resolveElapsedTime($startedAt);
    }

    private function resolveElapsedTime(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    private function buildUrl(string $name): string
    {
        $name = trim($name);

        if (preg_match('#^https?://#i', $name) === 1) {
            return $name;
        }

        return "https://{$name}";
    }

    private function extractBody(Response $response, HttpMethod $method): ?string
    {
        if ($method === HttpMethod::Head) {
            return null;
        }

        $body = $response->body();

        if ($body === '') {
            return null;
        }

        if (strlen($body) <= self::MAX_BODY_LENGTH) {
            return $body;
        }

        return substr($body, 0, self::MAX_BODY_LENGTH);
    }
}

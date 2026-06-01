<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;

class TelegramApiService
{
    private const MEMBER_STATUSES = ['creator', 'administrator', 'member', 'restricted'];

    private const BOT_NOT_IN_CHAT_ERROR_FRAGMENTS = [
        'user not found',
        'PARTICIPANT_ID_INVALID',
    ];

    public function botName(): string
    {
        return (string) config('services.telegram.bot_name');
    }

    public function isBotInChat(string $chatId): bool
    {
        return $this->validateBotInChat($chatId) === null;
    }

    /**
     * @return null if the bot is in the chat, otherwise a user-facing validation message
     */
    public function validateBotInChat(string $chatId): ?string
    {
        $botUserId = $this->getBotUserId();

        if ($botUserId === null) {
            return 'Telegram-бот не настроен.';
        }

        $response = Http::get($this->apiUrl('getChatMember'), [
            'chat_id' => $chatId,
            'user_id' => $botUserId,
        ]);

        $data = $response->json();

        if (! is_array($data)) {
            return 'Не удалось проверить Telegram Chat ID.';
        }

        if (! ($data['ok'] ?? false)) {
            $description = (string) ($data['description'] ?? '');

            if ($description === '') {
                return 'Не удалось проверить Telegram Chat ID.';
            }

            if ($this->isBotNotInChatError($description)) {
                return "Добавьте бота {$this->botName()} в указанный канал или чат.";
            }

            return $this->formatTelegramError($description);
        }

        $status = $data['result']['status'] ?? '';

        if (in_array($status, self::MEMBER_STATUSES, true)) {
            return null;
        }

        return "Добавьте бота {$this->botName()} в указанный канал или чат.";
    }

    private function isBotNotInChatError(string $description): bool
    {
        foreach (self::BOT_NOT_IN_CHAT_ERROR_FRAGMENTS as $fragment) {
            if (stripos($description, $fragment) !== false) {
                return true;
            }
        }

        return false;
    }

    private function formatTelegramError(string $description): string
    {
        $message = preg_replace('/^Bad Request:\s*/i', '', $description);

        return $message !== '' ? $message : $description;
    }

    private function getBotUserId(): ?int
    {
        $botId = config('services.telegram.bot_id');

        if ($botId === null || $botId === '') {
            return null;
        }

        return (int) $botId;
    }

    private function apiUrl(string $method): string
    {
        $token = config('services.telegram.token');

        return "https://api.telegram.org/bot{$token}/{$method}";
    }
}

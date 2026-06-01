<?php

namespace App\Rules;

use App\Services\Telegram\TelegramApiService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TelegramBotInChat implements ValidationRule
{
    public function __construct(
        private readonly TelegramApiService $telegram,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        $error = $this->telegram->validateBotInChat($value);

        if ($error !== null) {
            $fail($error);
        }
    }
}

<?php

namespace App\Http\Requests\NotificationSetting;

use App\Http\Requests\AuthenticatedRequest;
use App\Rules\TelegramBotInChat;

class UpdateNotificationSettingRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'tg_chat_id' => ['sometimes', 'string', 'max:255', app(TelegramBotInChat::class)],
            'notify_on_shutdown' => ['sometimes', 'boolean'],
            'notify_on_recovery' => ['sometimes', 'boolean'],
        ];
    }
}

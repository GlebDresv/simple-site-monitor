<?php

namespace App\Notifications;

use App\Enums\DomainStatus;
use App\Models\Domain;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class DomainStatusChanged extends Notification
{
    public function __construct(
        public Domain $domain,
        public DomainStatus $previousStatus,
        public DomainStatus $newStatus,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $message = match ($this->newStatus) {
            DomainStatus::Down => "Домен {$this->domain->name} недоступен",
            DomainStatus::Up => "Домен {$this->domain->name} восстановлен",
            DomainStatus::Unknown => "Статус домена {$this->domain->name} неизвестен",
        };

        return TelegramMessage::create()->content($message);
    }
}

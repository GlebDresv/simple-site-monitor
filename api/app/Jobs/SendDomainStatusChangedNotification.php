<?php

namespace App\Jobs;

use App\Enums\DomainStatus;
use App\Models\Domain;
use App\Models\NotificationSetting;
use App\Notifications\DomainStatusChanged;
use App\Services\Telegram\TelegramRateLimitParser;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use NotificationChannels\Telegram\Exceptions\CouldNotSendNotification;

class SendDomainStatusChangedNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct(
        public NotificationSetting $notificationSetting,
        public Domain $domain,
        public DomainStatus $previousStatus,
        public DomainStatus $newStatus,
    ) {}

    public function retryUntil(): DateTime
    {
        return now()->addDay()->toDateTime();
    }

    public function handle(): void
    {
        try {
            $this->notificationSetting->notifyNow(
                new DomainStatusChanged($this->domain, $this->previousStatus, $this->newStatus),
            );
        } catch (CouldNotSendNotification $exception) {
            $retryAfter = TelegramRateLimitParser::retryAfter($exception);

            if ($retryAfter !== null) {
                $this->release($retryAfter);

                return;
            }

            throw $exception;
        }
    }
}

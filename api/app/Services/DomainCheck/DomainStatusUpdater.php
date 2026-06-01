<?php

namespace App\Services\DomainCheck;

use App\Enums\DomainStatus;
use App\Jobs\SendDomainStatusChangedNotification;
use App\Models\CheckSetting;
use App\Models\Domain;

class DomainStatusUpdater
{
    public function __construct(
        private readonly DomainStatusStore $store,
    ) {}

    public function handleStatusChange(
        Domain $domain,
        CheckSetting $checkSetting,
        DomainStatus $newStatus,
    ): void {
        $previousStatus = $this->store->get($domain);

        $this->store->put($domain, $newStatus);

        if ($previousStatus === $newStatus) {
            return;
        }

        $domain->last_status = $newStatus;
        $domain->save();

        if ($previousStatus === null || $previousStatus === DomainStatus::Unknown) {
            return;
        }

        $checkSetting->loadMissing('notificationSetting');

        $notificationSetting = $checkSetting->notificationSetting;

        if ($notificationSetting === null) {
            return;
        }

        if ($newStatus === DomainStatus::Down && ! $notificationSetting->notify_on_shutdown) {
            return;
        }

        if ($newStatus === DomainStatus::Up && ! $notificationSetting->notify_on_recovery) {
            return;
        }

        if ($newStatus === DomainStatus::Unknown) {
            return;
        }

        SendDomainStatusChangedNotification::dispatch(
            $notificationSetting,
            $domain,
            $previousStatus,
            $newStatus,
        );
    }
}

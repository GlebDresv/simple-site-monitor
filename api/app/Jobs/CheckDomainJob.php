<?php

namespace App\Jobs;

use App\Models\CheckSetting;
use App\Models\Domain;
use App\Services\DomainCheck\DomainChecker;
use App\Services\DomainCheck\DomainStatusUpdater;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckDomainJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public const QUEUE = 'domain-checks';

    public int $uniqueFor = 120;

    public function __construct(
        public Domain $domain,
        public CheckSetting $checkSetting,
    ) {
        $this->onQueue(self::QUEUE);
    }

    public function uniqueId(): string
    {
        return (string) $this->domain->id;
    }

    public function handle(DomainChecker $checker, DomainStatusUpdater $statusUpdater): void
    {
        $checkLog = $checker->check($this->domain, $this->checkSetting);

        $statusUpdater->handleStatusChange(
            $this->domain,
            $this->checkSetting,
            $checker->resolveStatus($checkLog->response_code),
        );
    }
}

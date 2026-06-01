<?php

namespace App\Jobs;

use App\Models\CheckSetting;
use App\Models\Domain;
use App\Services\DomainCheck\DomainChecker;
use App\Services\DomainCheck\DomainStatusUpdater;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class CheckDomainJob implements ShouldQueue
{
    use Queueable;

    public const QUEUE = 'domain-checks';

    public function __construct(
        public Domain $domain,
        public CheckSetting $checkSetting,
    ) {
        $this->onQueue(self::QUEUE);
    }

    /**
     * @return list<WithoutOverlapping>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping((string) $this->domain->id))->expireAfter(120),
        ];
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

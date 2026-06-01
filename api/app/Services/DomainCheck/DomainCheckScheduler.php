<?php

namespace App\Services\DomainCheck;

use App\Enums\CheckInterval;
use App\Jobs\CheckDomainJob;
use App\Models\CheckSetting;
use Illuminate\Support\Carbon;

class DomainCheckScheduler
{
    public function run(?Carbon $moment = null): void
    {
        $moment ??= now();

        /** @var list<CheckInterval> $matchingIntervals */
        $matchingIntervals = CheckInterval::matchingMinute($moment->minute);

        if ($matchingIntervals === []) {
            return;
        }

        $checkSettings = CheckSetting::query()
            ->with('domains')
            ->whereIn('check_interval', $matchingIntervals)
            ->get();

        foreach ($checkSettings as $setting) {
            foreach ($setting->domains as $domain) {
                CheckDomainJob::dispatch($domain, $setting);
            }
        }
    }
}

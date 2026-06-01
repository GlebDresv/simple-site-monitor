<?php

namespace App\Services\DomainCheck;

use App\Models\CheckLog;
use Illuminate\Support\Carbon;

class CheckLogPruner
{
    public function prune(?Carbon $moment = null): int
    {
        $retentionDays = config('domain_check.check_log_retention_days');
        $cutoff = ($moment ?? now())->subDays($retentionDays);

        return CheckLog::query()
            ->where('checked_at', '<', $cutoff)
            ->delete();
    }
}

<?php

namespace App\Console\Commands;

use App\Services\DomainCheck\CheckLogPruner;
use Illuminate\Console\Command;

class PruneCheckLogsCommand extends Command
{
    protected $signature = 'check-logs:prune';

    protected $description = 'Delete check logs older than the retention period';

    public function handle(CheckLogPruner $pruner): int
    {
        $deleted = $pruner->prune();

        $days = config('domain_check.check_log_retention_days');
        $this->info("Deleted {$deleted} check log(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}

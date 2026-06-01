<?php

namespace App\Services\DomainCheck;

use App\Enums\DomainStatus;
use App\Models\Domain;
use Illuminate\Support\Facades\Redis;

class DomainStatusStore
{
    private const TTL_SECONDS = 86400;

    public function get(Domain $domain): ?DomainStatus
    {
        $value = Redis::get($this->key($domain));

        if ($value !== null) {
            return DomainStatus::from($value);
        }

        $status = $domain->last_status;

        if ($status === null) {
            return null;
        }

        $this->put($domain, $status);

        return $status;
    }

    public function put(Domain $domain, DomainStatus $status): void
    {
        Redis::setex($this->key($domain), self::TTL_SECONDS, $status->value);
    }

    public function forget(Domain $domain): void
    {
        Redis::del($this->key($domain));
    }

    private function key(Domain $domain): string
    {
        return "domain:{$domain->id}:status";
    }
}

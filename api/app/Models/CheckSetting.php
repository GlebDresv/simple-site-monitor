<?php

namespace App\Models;

use App\Enums\CheckInterval;
use App\Enums\HttpMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CheckSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'request_timeout',
        'method',
        'check_interval',
        'notification_settings_id',
    ];

    protected function casts(): array
    {
        return [
            'request_timeout' => 'integer',
            'method' => HttpMethod::class,
            'check_interval' => CheckInterval::class,
        ];
    }

    public function notificationSetting(): BelongsTo
    {
        return $this->belongsTo(NotificationSetting::class, 'notification_settings_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'check_settings_id');
    }
}

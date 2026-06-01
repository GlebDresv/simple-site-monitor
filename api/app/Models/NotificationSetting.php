<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class NotificationSetting extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'name',
        'tg_chat_id',
        'notify_on_shutdown',
        'notify_on_recovery',
        'debounce_interval',
    ];

    protected function casts(): array
    {
        return [
            'notify_on_shutdown' => 'boolean',
            'notify_on_recovery' => 'boolean',
            'debounce_interval' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkSettings(): HasMany
    {
        return $this->hasMany(CheckSetting::class, 'notification_settings_id');
    }

    public function routeNotificationForTelegram(): string
    {
        return $this->tg_chat_id;
    }
}

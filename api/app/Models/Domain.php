<?php

namespace App\Models;

use App\Enums\DomainStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'check_settings_id',
    ];

    protected function casts(): array
    {
        return [
            'last_status' => DomainStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkSetting(): BelongsTo
    {
        return $this->belongsTo(CheckSetting::class, 'check_settings_id');
    }

    public function checkLogs(): HasMany
    {
        return $this->hasMany(CheckLog::class);
    }
}

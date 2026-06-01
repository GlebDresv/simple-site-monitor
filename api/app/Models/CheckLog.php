<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain_id',
        'checked_at',
        'response_code',
        'response_time',
        'response_headers',
        'redirects_count',
        'response_body',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
            'response_code' => 'integer',
            'response_time' => 'integer',
            'response_headers' => 'array',
            'redirects_count' => 'integer',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}

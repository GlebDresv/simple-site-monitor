<?php

namespace App\Http\Requests\CheckSetting;

use App\Enums\CheckInterval;
use App\Enums\HttpMethod;
use App\Http\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateCheckSettingRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'request_timeout' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'method' => ['sometimes', Rule::enum(HttpMethod::class)],
            'check_interval' => ['sometimes', Rule::enum(CheckInterval::class)],
            'notification_settings_id' => [
                'sometimes',
                'integer',
                Rule::exists('notification_settings', 'id')->where('user_id', $this->user()->id),
            ],
        ];
    }
}

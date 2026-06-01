<?php

namespace App\Http\Requests\CheckSetting;

use App\Enums\CheckInterval;
use App\Enums\HttpMethod;
use App\Http\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class StoreCheckSettingRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'request_timeout' => ['required', 'integer', 'min:1', 'max:5'],
            'method' => ['required', Rule::enum(HttpMethod::class)],
            'check_interval' => ['required', Rule::enum(CheckInterval::class)],
            'notification_settings_id' => [
                'required',
                'integer',
                Rule::exists('notification_settings', 'id')->where('user_id', $this->user()->id),
            ],
        ];
    }
}

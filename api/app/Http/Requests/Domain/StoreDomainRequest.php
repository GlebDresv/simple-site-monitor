<?php

namespace App\Http\Requests\Domain;

use App\Http\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class StoreDomainRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:253',
                'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i',
                Rule::unique('domains', 'name')->where('user_id', $this->user()->id),
            ],
            'check_settings_id' => [
                'nullable',
                'integer',
                Rule::exists('check_settings', 'id')->where(
                    fn ($query) => $query->whereIn(
                        'notification_settings_id',
                        $this->user()->notificationSettings()->select('id')
                    )
                ),
            ],
        ];
    }
}

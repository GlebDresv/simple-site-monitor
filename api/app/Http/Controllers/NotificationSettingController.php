<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationSetting\StoreNotificationSettingRequest;
use App\Http\Requests\NotificationSetting\UpdateNotificationSettingRequest;
use App\Models\NotificationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $settings = $request->user()->notificationSettings()->latest()->get();

        return response()
            ->json($settings)
            ->header('X-Total-Count', (string) $settings->count());
    }

    public function store(StoreNotificationSettingRequest $request): JsonResponse
    {
        $notificationSetting = $request->user()->notificationSettings()->create(
            $request->validated()
        );

        return response()->json($notificationSetting, 201);
    }

    public function show(Request $request, NotificationSetting $notificationSetting): JsonResponse
    {
        $this->authorizeOwnership($request, $notificationSetting);

        return response()->json($notificationSetting);
    }

    public function update(
        UpdateNotificationSettingRequest $request,
        NotificationSetting $notificationSetting
    ): JsonResponse {
        $this->authorizeOwnership($request, $notificationSetting);

        $notificationSetting->update($request->validated());

        return response()->json($notificationSetting);
    }

    public function destroy(Request $request, NotificationSetting $notificationSetting): JsonResponse
    {
        $this->authorizeOwnership($request, $notificationSetting);

        $notificationSetting->delete();

        return response()->json(null, 204);
    }

    private function authorizeOwnership(Request $request, NotificationSetting $notificationSetting): void
    {
        abort_unless($notificationSetting->user_id === $request->user()->id, 403);
    }
}

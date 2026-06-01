<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckSetting\StoreCheckSettingRequest;
use App\Http\Requests\CheckSetting\UpdateCheckSettingRequest;
use App\Models\CheckSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckSettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $settings = CheckSetting::query()
            ->whereHas('notificationSetting', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with('notificationSetting')
            ->latest()
            ->get();

        return response()
            ->json($settings)
            ->header('X-Total-Count', (string) $settings->count());
    }

    public function store(StoreCheckSettingRequest $request): JsonResponse
    {
        $checkSetting = CheckSetting::create($request->validated());

        return response()->json($checkSetting->load('notificationSetting'), 201);
    }

    public function show(Request $request, CheckSetting $checkSetting): JsonResponse
    {
        $this->authorizeOwnership($request, $checkSetting);

        return response()->json($checkSetting->load('notificationSetting'));
    }

    public function update(
        UpdateCheckSettingRequest $request,
        CheckSetting $checkSetting
    ): JsonResponse {
        $this->authorizeOwnership($request, $checkSetting);

        $checkSetting->update($request->validated());

        return response()->json($checkSetting->load('notificationSetting'));
    }

    public function destroy(Request $request, CheckSetting $checkSetting): JsonResponse
    {
        $this->authorizeOwnership($request, $checkSetting);

        $checkSetting->delete();

        return response()->json(null, 204);
    }

    private function authorizeOwnership(Request $request, CheckSetting $checkSetting): void
    {
        abort_unless(
            $checkSetting->notificationSetting?->user_id === $request->user()->id,
            403
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\DomainStatus;
use App\Http\Requests\Domain\StoreDomainRequest;
use App\Http\Requests\Domain\UpdateDomainRequest;
use App\Models\Domain;
use App\Services\DomainCheck\DomainStatusStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $domains = $request->user()->domains()->with('checkSetting')->latest()->get();

        return response()
            ->json($domains)
            ->header('X-Total-Count', (string) $domains->count());
    }

    public function store(StoreDomainRequest $request): JsonResponse
    {
        $domain = $request->user()->domains()->create($request->validated());

        return response()->json($domain->load('checkSetting'), 201);
    }

    public function show(Request $request, Domain $domain): JsonResponse
    {
        $this->authorizeOwnership($request, $domain);

        return response()->json($domain->load('checkSetting'));
    }

    public function update(
        UpdateDomainRequest $request,
        Domain $domain,
        DomainStatusStore $store,
    ): JsonResponse {
        $this->authorizeOwnership($request, $domain);

        $validated = $request->validated();

        if (array_key_exists('check_settings_id', $validated) && $validated['check_settings_id'] === null) {
            $domain->last_status = DomainStatus::Unknown;
            $store->forget($domain);
        }

        $domain->fill($validated);
        $domain->save();

        return response()->json($domain->load('checkSetting'));
    }

    public function destroy(Request $request, Domain $domain): JsonResponse
    {
        $this->authorizeOwnership($request, $domain);

        $domain->delete();

        return response()->json(null, 204);
    }

    private function authorizeOwnership(Request $request, Domain $domain): void
    {
        abort_unless($domain->user_id === $request->user()->id, 403);
    }
}

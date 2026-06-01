<?php

namespace App\Http\Controllers;

use App\Models\CheckLog;
use App\Models\Domain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckLogController extends Controller
{
    private const ALLOWED_SORT_FIELDS = ['checked_at', 'id', 'created_at'];

    public function index(Request $request): JsonResponse
    {
        $filter = json_decode($request->query('filter', '{}'), true) ?: [];
        $sort = json_decode($request->query('sort', '["checked_at","DESC"]'), true) ?: ['checked_at', 'DESC'];
        $range = json_decode($request->query('range', '[0,19]'), true) ?: [0, 19];

        $query = CheckLog::query()
            ->whereHas('domain', fn ($q) => $q->where('user_id', $request->user()->id));

        if (! empty($filter['domain_id'])) {
            $domainId = (int) $filter['domain_id'];

            abort_unless(
                Domain::query()
                    ->where('id', $domainId)
                    ->where('user_id', $request->user()->id)
                    ->exists(),
                403
            );

            $query->where('domain_id', $domainId);
        }

        $total = $query->count();

        $sortField = in_array($sort[0] ?? null, self::ALLOWED_SORT_FIELDS, true)
            ? $sort[0]
            : 'checked_at';
        $sortOrder = strtolower($sort[1] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $offset = max(0, (int) ($range[0] ?? 0));
        $limit = max(1, (int) ($range[1] ?? 19) - $offset + 1);

        $logs = $query
            ->with('domain')
            ->orderBy($sortField, $sortOrder)
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()
            ->json($logs)
            ->header('X-Total-Count', (string) $total);
    }

    public function show(Request $request, CheckLog $checkLog): JsonResponse
    {
        $this->authorizeOwnership($request, $checkLog);

        return response()->json($checkLog->load('domain'));
    }

    private function authorizeOwnership(Request $request, CheckLog $checkLog): void
    {
        abort_unless($checkLog->domain?->user_id === $request->user()->id, 403);
    }
}

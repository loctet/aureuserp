<?php

namespace Webkul\Contracts\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Contracts\Services\AllocatablePoolQueryService;

class AllocatablePoolController
{
    public function __construct(
        protected AllocatablePoolQueryService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $month = $request->string('month')->toString() ?: now()->startOfMonth()->toDateString();
        $payload = $this->service->forMonth($month)->values();

        return response()->json([
            'data' => $payload,
            'meta' => [
                'month' => $month,
            ],
        ]);
    }
}

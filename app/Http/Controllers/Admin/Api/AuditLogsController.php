<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = AuditLog::query()->with('user:id,name')->latest();

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(fn ($q) => $q->where('action', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        $logs = $query->paginate(30)->withQueryString();

        return response()->json([
            'data' => $logs->getCollection()->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user' => $log->user?->name ?? 'System',
                'ip' => $log->ip,
                'changes' => $log->changes,
                'created_at' => $log->created_at,
            ])->values(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\JobStatus;
use App\Enums\PageStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ContactEnquiry;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Page;
use App\Models\Service;
use App\Models\Solution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('role');

        return response()->json([
            'stats' => [
                'pages' => Page::query()->where('status', PageStatus::Published)->count(),
                'articles' => Article::query()->published()->count(),
                'services' => Service::query()->published()->count(),
                'solutions' => Solution::query()->published()->count(),
                'active_jobs' => Job::query()->open()->count(),
                'applications' => JobApplication::query()->count(),
                'new_applications' => JobApplication::query()->where('status', 'new')->count(),
                'enquiries' => ContactEnquiry::query()->where('status', 'new')->count(),
            ],
            'recent_enquiries' => ContactEnquiry::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'email', 'subject', 'status', 'created_at']),
            'recent_applications' => JobApplication::query()
                ->with('job:id,title')
                ->latest()
                ->limit(5)
                ->get(['id', 'job_id', 'first_name', 'last_name', 'email', 'status', 'created_at']),
            'open_jobs' => Job::query()
                ->where('status', JobStatus::Open)
                ->latest()
                ->limit(4)
                ->get(['id', 'title', 'location', 'closing_at']),
            'recent_pages' => Page::query()
                ->latest('updated_at')
                ->limit(5)
                ->get(['id', 'title', 'slug', 'status', 'updated_at']),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('role', 'avatar');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'initials' => $user->initials(),
                'role' => $user->role?->name,
                'role_slug' => $user->role?->slug,
                'avatar_url' => $user->avatar?->url(),
            ],
            'permissions' => $user->permissionSlugs()->values(),
        ]);
    }
}

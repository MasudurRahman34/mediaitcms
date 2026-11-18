<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = JobApplication::query()->with('job:id,title');

        if ($jobId = $request->integer('job_id')) {
            $query->where('job_id', $jobId);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(fn ($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        $applications = $query->latest()->paginate(20)->withQueryString();

        return response()->json([
            'data' => $applications->getCollection()->map(fn (JobApplication $application) => $this->present($application))->values(),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'total' => $applications->total(),
            ],
        ]);
    }

    public function show(JobApplication $application): JsonResponse
    {
        $application->load(['job:id,title,slug', 'notes.user:id,name']);

        return response()->json([
            'data' => [
                ...$this->present($application, detailed: true),
                'cover_letter' => $application->cover_letter,
                'linkedin_url' => $application->linkedin_url,
                'notes' => $application->notes->map(fn ($note) => [
                    'id' => $note->id,
                    'body' => $note->body,
                    'author' => $note->user?->name ?? 'System',
                    'created_at' => $note->created_at,
                ])->values(),
            ],
        ]);
    }

    public function update(Request $request, JobApplication $application): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(ApplicationStatus::values())],
        ]);

        $application->update(['status' => $data['status']]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'application.status-updated',
            'description' => $application->fullName().' → '.$data['status'],
            'auditable_type' => $application->getMorphClass(),
            'auditable_id' => $application->id,
            'changes' => ['status' => $data['status']],
            'ip' => $request->ip(),
        ]);

        return response()->json(['data' => $this->present($application->fresh('job'))]);
    }

    public function destroy(JobApplication $application): JsonResponse
    {
        Storage::disk('local')->delete($application->cv_path);
        $application->delete();

        return response()->json(['data' => ['ok' => true]]);
    }

    public function storeNote(Request $request, JobApplication $application): JsonResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:4000'],
        ]);

        $note = $application->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return response()->json([
            'data' => [
                'id' => $note->id,
                'body' => $note->body,
                'author' => $request->user()->name,
                'created_at' => $note->created_at,
            ],
        ], 201);
    }

    /**
     * Stream the CV from private storage. Never exposed via a public URL.
     */
    public function downloadCv(Request $request, JobApplication $application): StreamedResponse
    {
        $disk = Storage::disk('local');

        abort_unless($disk->exists($application->cv_path), 404, 'CV not found.');

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'application.cv-downloaded',
            'description' => $application->fullName(),
            'auditable_type' => $application->getMorphClass(),
            'auditable_id' => $application->id,
            'ip' => $request->ip(),
        ]);

        return $disk->download($application->cv_path, $application->cv_original_name);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = JobApplication::query()->with('job:id,title');

        if ($jobId = $request->integer('job_id')) {
            $query->where('job_id', $jobId);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $filename = 'applications-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Phone', 'Job', 'Status', 'Applied', 'CV']);

            $query->latest()->each(function (JobApplication $application) use ($handle) {
                fputcsv($handle, [
                    $application->fullName(),
                    $application->email,
                    $application->phone,
                    $application->job?->title,
                    $application->status->value,
                    $application->created_at?->toDateTimeString(),
                    $application->cv_original_name,
                ]);
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /** @return array<string, mixed> */
    private function present(JobApplication $application, bool $detailed = false): array
    {
        return [
            'id' => $application->id,
            'name' => $application->fullName(),
            'initials' => $application->initials(),
            'first_name' => $application->first_name,
            'last_name' => $application->last_name,
            'email' => $application->email,
            'phone' => $application->phone,
            'status' => $application->status->value,
            'job' => $application->job ? ['id' => $application->job->id, 'title' => $application->job->title] : null,
            'cv' => [
                'name' => $application->cv_original_name,
                'size' => $application->cv_size,
                'url' => route('admin.api.applications.cv', $application),
            ],
            'created_at' => $application->created_at,
            'notes_count' => $detailed ? $application->notes->count() : null,
        ];
    }
}

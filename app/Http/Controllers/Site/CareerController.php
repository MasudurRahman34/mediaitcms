<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Mail\ApplicationConfirmation;
use App\Mail\NewJobApplication;
use App\Models\Job;
use App\Models\JobApplication;
use App\Services\Seo\SeoManager;
use App\Services\SettingsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(SeoManager $seo): View
    {
        $jobs = Job::query()->open()->ordered()->get();

        return view('site.careers.index', [
            'jobs' => $jobs,
            'seo' => $seo->simple(
                'Careers',
                'Join Media IT Soft — software engineers, AI specialists and IT professionals building products that matter.',
            ),
        ]);
    }

    public function show(Job $job, SeoManager $seo): View
    {
        abort_if($job->status->value === 'draft', 404);

        return view('site.careers.show', [
            'job' => $job,
            'acceptingApplications' => $job->isAcceptingApplications(),
            'seo' => $seo->job($job),
        ]);
    }

    public function apply(Request $request, Job $job, SettingsRepository $settings): RedirectResponse
    {
        abort_unless($job->isAcceptingApplications(), 410, 'This vacancy is no longer accepting applications.');

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'cover_letter' => ['nullable', 'string', 'max:8000'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'consent' => ['accepted'],
            // Honeypot: bots fill hidden fields.
            'website' => ['nullable', 'size:0'],
        ], [
            'consent.accepted' => 'Please confirm you consent to us processing your application.',
            'cv.mimes' => 'Please upload your CV as a PDF, DOC or DOCX file.',
            'cv.max' => 'Your CV must be 10MB or smaller.',
        ]);

        $file = $request->file('cv');
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs(
            'applicant-documents/'.date('Y/m'),
            Str::uuid().'.'.$extension,
            'local',
        );

        $application = JobApplication::create([
            'job_id' => $job->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'linkedin_url' => $data['linkedin_url'] ?? null,
            'cover_letter' => $data['cover_letter'] ?? null,
            'cv_path' => $path,
            'cv_original_name' => $file->getClientOriginalName(),
            'cv_mime' => $file->getMimeType() ?? 'application/octet-stream',
            'cv_size' => $file->getSize() ?: 0,
            'status' => 'new',
            'source' => 'website',
            'ip' => $request->ip(),
        ]);

        $inbox = $settings->get('notification_email', config('mail.from.address'));

        try {
            Mail::to($application->email)->send(new ApplicationConfirmation($application));
            if ($inbox) {
                Mail::to($inbox)->send(new NewJobApplication($application));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('careers.show', $job->slug)
            ->with('application_success', 'Thank you — your application has been received. We will be in touch.');
    }
}

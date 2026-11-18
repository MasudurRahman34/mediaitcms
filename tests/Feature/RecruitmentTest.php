<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Mail\ApplicationConfirmation;
use App\Mail\NewEnquiry;
use App\Models\ContactEnquiry;
use App\Models\JobApplication;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecruitmentTest extends TestCase
{
    public function test_application_can_be_submitted_with_a_cv(): void
    {
        Mail::fake();

        $job = $this->job();

        $response = $this->post('/careers/'.$job->slug.'/apply', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '07700900123',
            'cover_letter' => 'I would like to apply.',
            'consent' => '1',
            'cv' => UploadedFile::fake()->create('jane-cv.pdf', 120, 'application/pdf'),
        ]);

        $response->assertRedirect('/careers/'.$job->slug);

        $application = JobApplication::query()->firstOrFail();
        $this->assertSame('Jane', $application->first_name);
        $this->assertSame(ApplicationStatus::New, $application->status);

        // CV is stored on the private disk, never the public one.
        Storage::disk('local')->assertExists($application->cv_path);
        Storage::disk('public')->assertMissing($application->cv_path);
    }

    public function test_cv_is_required(): void
    {
        $job = $this->job();

        $this->post('/careers/'.$job->slug.'/apply', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'consent' => '1',
        ])->assertSessionHasErrors('cv');
    }

    public function test_disallowed_file_types_are_rejected(): void
    {
        $job = $this->job();

        $this->post('/careers/'.$job->slug.'/apply', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'consent' => '1',
            'cv' => UploadedFile::fake()->create('malware.exe', 10, 'application/x-msdownload'),
        ])->assertSessionHasErrors('cv');
    }

    public function test_closed_jobs_reject_applications(): void
    {
        $job = $this->job(['status' => 'closed']);

        $this->post('/careers/'.$job->slug.'/apply', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'consent' => '1',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ])->assertStatus(410);
    }

    public function test_applications_past_the_closing_date_are_rejected(): void
    {
        $job = $this->job(['closing_at' => now()->subDay()]);

        $this->post('/careers/'.$job->slug.'/apply', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'consent' => '1',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ])->assertStatus(410);
    }

    public function test_cv_download_requires_authentication(): void
    {
        $application = $this->makeApplication();

        $this->get('/admin/api/applications/'.$application->id.'/cv')->assertRedirect('/admin/login');
    }

    public function test_authorised_staff_can_download_a_cv(): void
    {
        $application = $this->makeApplication();

        $response = $this->actingAs($this->admin())
            ->get('/admin/api/applications/'.$application->id.'/cv');

        $response->assertOk();
        $response->assertDownload($application->cv_original_name);
    }

    public function test_recruiter_role_can_download_cvs_but_not_edit_articles(): void
    {
        $recruiter = $this->userWithRole(Role::RECRUITER);
        $this->grant($recruiter, ['applications.view', 'careers.manage']);

        $application = $this->makeApplication();

        $this->actingAs($recruiter)
            ->get('/admin/api/applications/'.$application->id.'/cv')
            ->assertOk();

        $this->actingAs($recruiter)
            ->get('/admin/api/articles')
            ->assertForbidden();
    }

    public function test_application_status_can_be_updated(): void
    {
        $application = $this->makeApplication();

        $this->actingAs($this->admin())
            ->patchJson('/admin/api/applications/'.$application->id, ['status' => 'shortlisted'])
            ->assertOk();

        $this->assertSame(ApplicationStatus::Shortlisted, $application->fresh()->status);
    }

    public function test_internal_notes_can_be_added(): void
    {
        $application = $this->makeApplication();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/admin/api/applications/'.$application->id.'/notes', [
                'body' => 'Strong technical background.',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('application_notes', [
            'job_application_id' => $application->id,
            'body' => 'Strong technical background.',
        ]);
    }

    public function test_applications_can_be_filtered_by_status(): void
    {
        $application = $this->makeApplication();
        $this->makeApplication(['email' => 'other@example.com', 'status' => 'rejected']);

        $response = $this->actingAs($this->admin())
            ->getJson('/admin/api/applications?status=new')
            ->assertOk();

        $this->assertCount(1, $response->json('data'));
    }

    public function test_csv_export_includes_applications(): void
    {
        $application = $this->makeApplication();

        $response = $this->actingAs($this->admin())
            ->get('/admin/api/applications-export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
    }

    public function test_contact_enquiry_is_stored_and_notifies(): void
    {
        Mail::fake();

        $this->post('/contact', [
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'subject' => 'New project',
            'message' => 'We need a platform built.',
            'consent' => '1',
        ])->assertRedirect('/contact');

        $this->assertDatabaseHas('contact_enquiries', ['email' => 'alice@example.com']);

        Mail::assertSent(NewEnquiry::class);
    }

    public function test_contact_form_rejects_honeypot_submissions(): void
    {
        $this->post('/contact', [
            'name' => 'Spam Bot',
            'email' => 'spam@example.com',
            'message' => 'Buy my product',
            'consent' => '1',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, ContactEnquiry::query()->count());
    }

    public function test_contact_form_requires_consent(): void
    {
        $this->post('/contact', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'message' => 'Hello',
        ])->assertSessionHasErrors('consent');
    }

    public function test_applicants_receive_a_confirmation_email(): void
    {
        Mail::fake();

        $this->post('/careers/'.$this->job()->slug.'/apply', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'consent' => '1',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        Mail::assertSent(ApplicationConfirmation::class);
    }

    private function makeApplication(array $overrides = []): JobApplication
    {
        $path = 'applicant-documents/test.pdf';
        Storage::disk('local')->put($path, 'pdf-content');

        return JobApplication::query()->create(array_merge([
            'job_id' => $this->job()->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'cv_path' => $path,
            'cv_original_name' => 'jane-cv.pdf',
            'cv_mime' => 'application/pdf',
            'cv_size' => 11,
            'status' => ApplicationStatus::New,
        ], $overrides));
    }

    private function grant(User $user, array $permissions): void
    {
        foreach ($permissions as $slug) {
            Permission::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $slug, 'group' => 'test'],
            );
        }

        $user->role->permissions()->sync(
            Permission::query()->whereIn('slug', $permissions)->pluck('id'),
        );

        $user->load('role.permissions');
    }
}

<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Mail\NewEnquiry;
use App\Models\ContactEnquiry;
use App\Services\Seo\SeoManager;
use App\Services\SettingsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __invoke(SeoManager $seo): View
    {
        return view('site.contact', [
            'seo' => $seo->simple(
                'Contact',
                'Talk to Media IT Soft about your next software, AI or IT project. Based in Swindon, working across the UK.',
            ),
        ]);
    }

    public function store(Request $request, SettingsRepository $settings): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:160'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'size:0'],
        ]);

        $enquiry = ContactEnquiry::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'subject' => $data['subject'] ?? 'Website enquiry',
            'message' => $data['message'],
            'status' => 'new',
            'ip' => $request->ip(),
        ]);

        $inbox = $settings->get('notification_email', config('mail.from.address'));

        try {
            if ($inbox) {
                Mail::to($inbox)->send(new NewEnquiry($enquiry));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('contact')
            ->with('enquiry_success', 'Thanks for getting in touch. We aim to reply within one working day.');
    }
}

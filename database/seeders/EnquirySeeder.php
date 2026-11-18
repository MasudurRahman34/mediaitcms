<?php

namespace Database\Seeders;

use App\Models\ContactEnquiry;
use Illuminate\Database\Seeder;

class EnquirySeeder extends Seeder
{
    public function run(): void
    {
        $enquiries = [
            [
                'name' => 'Rachel Simmons',
                'email' => 'rachel.simmons@example.com',
                'phone' => '+44 7700 900451',
                'company' => 'PrintLab',
                'subject' => 'Phase two of the ordering portal',
                'message' => "Hi team,\n\nWe would like to scope the next phase of the ordering portal — specifically partner-level reporting and a customer reorder flow.\n\nCould we set up a call next week?\n\nThanks,\nRachel",
                'status' => 'new',
                'days' => 1,
            ],
            [
                'name' => 'Ian Fielding',
                'email' => 'ian.fielding@example.com',
                'phone' => null,
                'company' => 'Fielding & Co',
                'subject' => 'AI document review for a small firm',
                'message' => "We are a 12-partner firm looking at document review tooling. Is this something you would take on at our size, and what would a pilot look like?\n\nIan",
                'status' => 'read',
                'days' => 4,
            ],
            [
                'name' => 'Deborah Okafor',
                'email' => 'd.okafor@example.com',
                'phone' => '+44 7700 900782',
                'company' => 'Northgate Facilities',
                'subject' => 'Legacy system replacement',
                'message' => 'Our maintenance system is end of life and the vendor has stopped supporting it. We have around 40 engineers and three depots. Where would you start?',
                'status' => 'replied',
                'days' => 9,
            ],
        ];

        foreach ($enquiries as $enquiry) {
            ContactEnquiry::query()->updateOrCreate(
                ['email' => $enquiry['email'], 'subject' => $enquiry['subject']],
                [
                    'name' => $enquiry['name'],
                    'phone' => $enquiry['phone'],
                    'company' => $enquiry['company'],
                    'message' => $enquiry['message'],
                    'status' => $enquiry['status'],
                    'ip' => '127.0.0.1',
                    'created_at' => now()->subDays($enquiry['days']),
                ],
            );
        }
    }
}

<?php

namespace App\Mail;

use App\Models\ContactEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewEnquiry extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactEnquiry $enquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New website enquiry — '.$this->enquiry->subject,
            replyTo: [$this->enquiry->email],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.new-enquiry');
    }
}

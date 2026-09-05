<?php

namespace App\Mail;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewPendingAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Review $review, public string $adminUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New review to moderate',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.review-pending-admin',
        );
    }
}

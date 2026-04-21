<?php

namespace App\Jobs;

use App\Services\PHPMailerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public string $toEmail,
        public string $toName,
        public string $subject,
        public string $bodyHtml,
        public string $bodyText = '',
    ) {}

    public function handle(PHPMailerService $mailer): void
    {
        $mailer->sendNotificationEmail(
            $this->toEmail,
            $this->toName,
            $this->subject,
            $this->bodyHtml,
            $this->bodyText ?: strip_tags($this->bodyHtml),
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send notification email', [
            'to' => $this->toEmail,
            'subject' => $this->subject,
            'error' => $exception->getMessage(),
        ]);
    }
}

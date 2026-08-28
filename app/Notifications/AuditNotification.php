<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuditNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $action,
        public ?string $ip = null,
    ) {}

    /** @return array<int,string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string,mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'action' => $this->action,
            'ip' => $this->ip,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())->line("Auth event: {$this->action}");
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ExceptionOccurred extends Notification
{
    use Queueable;

    protected $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // Build the email message
    public function toMail($notifiable)
    {
        // Format the stack trace into a string
        $stackTrace = nl2br($this->exception->getTraceAsString());

        return (new MailMessage())
                    ->subject('Exception Occurred - SLSKey Admin Portal')
                    ->greeting('An Exception Occurred')
                    ->line('User: ' . Auth::user()?->user_identifier)
                    ->line('Message: ' . $this->exception->getMessage())
                    ->line('File: ' . $this->exception->getFile())
                    ->line('Line: ' . $this->exception->getLine())
                    ->line('Stack Trace:')
                    ->with('html', true) // Enable HTML for stack trace formatting
                    ->line($stackTrace);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

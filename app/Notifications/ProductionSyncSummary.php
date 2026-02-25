<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductionSyncSummary extends Notification
{
    use Queueable;

    protected $syncedData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $syncedData)
    {
        $this->syncedData = $syncedData;
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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('REGISTROS DE PRODUCCIÓN ENVIADOS A INFOR - ' . now()->format('d/m/Y H:i'))
            ->view('emails.production_summary', [
                'records' => $this->syncedData,
                'date'    => now()->format('d/m/Y H:i'),
            ]);
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

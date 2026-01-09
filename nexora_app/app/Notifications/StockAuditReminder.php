<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockAuditReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public $itemCount;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $itemCount)
    {
        $this->itemCount = $itemCount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('⚠️ Audit de Stock Requis - Nexora')
                    ->greeting('Bonjour ' . $notifiable->name . ',')
                    ->line('Il y a ' . $this->itemCount . ' articles qui n\'ont pas été audités depuis plus de 48 heures.')
                    ->line('Une vérification régulière du stock est essentielle pour éviter les écarts.')
                    ->action('Commencer l\'Audit', route('admin.inventory.audit'))
                    ->line('Merci de votre vigilance.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'item_count' => $this->itemCount,
            'message' => "Audit requis pour {$this->itemCount} articles."
        ];
    }
}

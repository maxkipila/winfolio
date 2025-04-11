<?php

namespace App\Notifications;

use App\Models\Award;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAwardNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $award;

    public function __construct(Award $award)
    {
        $this->award = $award;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Získali jste nový odznak: ' . $this->award->name)
            ->line('Gratulujeme! Právě jste získali nový odznak.')
            ->line('Odznak: ' . $this->award->name)
            ->line($this->award->description)
            ->action('Zobrazit detail', url('/awards'))
            ->line('Děkujeme, že používáte naši aplikaci!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'award_id' => $this->award->id,
            'award_name' => $this->award->name,
            'award_description' => $this->award->description,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Award;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AwardEarned extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Odznak, který byl získán.
     *
     * @var Award
     */
    protected $award;

    /**
     * Vytvoří novou instanci notifikace.
     *
     * @param Award $award Odznak, který byl získán
     * @return void
     */
    public function __construct(Award $award)
    {
        $this->award = $award;
    }

    /**
     * Získá kanály, na které bude notifikace odeslána.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Získá e-mailové znázornění notifikace.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Získal jsi nový odznak: ' . $this->award->name)
            ->greeting('Gratulujeme!')
            ->line('Právě jsi získal nový odznak: ' . $this->award->name)
            ->line($this->award->description)
            ->action('Zobrazit své odznaky', url('/profile/awards'))
            ->line('Děkujeme, že používáš naši aplikaci!');
    }

    /**
     * Získá databázové znázornění notifikace.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'award_id' => $this->award->id,
            'award_name' => $this->award->name,
            'award_icon' => $this->award->icon,
            'message' => 'Získal jsi nový odznak: ' . $this->award->name,
        ];
    }
}

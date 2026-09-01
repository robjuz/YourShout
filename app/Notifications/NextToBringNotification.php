<?php

namespace App\Notifications;

use App\Models\Consumable;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class NextToBringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Consumable $consumable,
        public User $user,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail'];

//        if ($notifiable->pushSubscriptions()->count() > 0) {
//            $channels[] = 'webpush';
//        }

        return $channels;
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $locale = session('locale', app()->getLocale());

        return (new WebPushMessage)
            ->title(__('notifications.next_to_bring_title', ['user' => $this->user->name], $locale))
            ->body(__('notifications.next_to_bring_body', [
                'user' => $this->user->name,
                'consumable' => $this->consumable->name,
                'unit' => $this->consumable->unit,
            ], $locale))
            ->badge('/favicon.png')
            ->data(['url' => route('consumables.show', $this->consumable)]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = session('locale', app()->getLocale());

        return (new MailMessage)
            ->subject(__('notifications.next_to_bring_title', ['user' => $this->user->name], $locale))
            ->line(__('notifications.next_to_bring_greeting', ['user' => $this->user->name], $locale))
            ->line(__('notifications.next_to_bring_body', [
                'user' => $this->user->name,
                'consumable' => $this->consumable->name,
                'unit' => $this->consumable->unit,
            ], $locale))
            ->action(__('notifications.view_consumable', url(route('consumables.show', $this->consumable))))
            ->line(__('notifications.thank_you', [], $locale));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'consumable_id' => $this->consumable->id,
            'user_id' => $this->user->id,
        ];
    }
}

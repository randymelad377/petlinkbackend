<?php

namespace App\Notifications\users;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserNotification extends Notification
{
    use Queueable;

    protected $user;
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {

        $username = ucfirst($this->user->firstName) . " " . ucfirst($this->user->lastName);

        return [
            "title" => "newUser",
            "message" => "New user ({$username})",
            "user_id" => $this->user->public_id,
            "image_path" => $this->user->user_img_path
        ];
    }
}

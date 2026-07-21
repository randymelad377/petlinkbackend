<?php

namespace App\Notifications\pets\users;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewVerifiedNotification extends Notification
{
    use Queueable;

    protected $pet;
    protected $user;
    public function __construct($pet, $user)
    {
        $this->pet = $pet;
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
        return [
            "title" => "newVerifiedPet",
            "message" => ucfirst($this->user->firstName) . " " . ucfirst($this->user->lastName) . " added new pet.",
            "pet_id" => $this->pet->public_id,
            "image_path" => $this->user->user_img_path
        ];
    }
}

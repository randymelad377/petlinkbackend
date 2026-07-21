<?php

namespace App\Notifications\admin\pets;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPetNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $receiver;
    protected $pet;
    public function __construct($message, $receiver, $pet)
    {
        $this->message = $message;
        $this->receiver = $receiver;
        $this->pet = $pet;
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
            "title" => "adminSendPet",
            "message" => "Admin: " . $this->message,
            "pet_id" => $this->pet->public_id,
            "image_path" => "defaults/defaultPhp.png"
        ];
    }
}

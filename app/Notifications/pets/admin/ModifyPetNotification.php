<?php

namespace App\Notifications\pets\admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModifyPetNotification extends Notification
{
    use Queueable;

    protected $pet;
    protected $user;
    protected $action;
    protected $interested;


    public function __construct($pet, $user, $action, $interested)
    {
        $this->pet = $pet;
        $this->user = $user;
        $this->action = $action;
        $this->interested = $interested;
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
        $isInterestedReceiver = $notifiable->id === $this->interested?->id;

        $mesage = match ($this->action) {
            "approve" => "Your pet has been approved",
            "cancelTransaction" => "Your transaction with " . ($isInterestedReceiver ? "{$this->user->firstName} {$this->user->lastName}" : "{$notifiable->firstName} {$notifiable->lastName}") . " has been cancelled by admin.",
            "decline" => "Your pet has been declined",
            "softDelete" => "Your verified pet {$this->pet->species->species} has been deleted by the admin.",
            "back" => "Admin decided to back your deleted pet.",
            default => "Pet."
        };

        $pet_id = match ($this->action) {
            "declined" => null,
            "softDelete" => null,
            default => $this->pet->public_id
        };

        return [
            "title" => "modifyPet",
            "message" => $mesage,
            "pet_id" => $pet_id,
            "image_path" => "defaults/defaultPhp.png",
        ];
    }
}

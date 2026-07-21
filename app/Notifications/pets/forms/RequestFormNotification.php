<?php

namespace App\Notifications\pets\forms;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestFormNotification extends Notification
{
    use Queueable;

    protected $form;
    protected $requester;
    protected $receiver;
    public function __construct($form, $requester, $receiver)
    {
        $this->form = $form;
        $this->requester = $requester;
        $this->receiver = $receiver;
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
            "title" => "requestForm",
            "message" => ucfirst($this->requester->firstName) . " " . ucfirst($this->requester->lastName) . " request to your pet.",
            "form_id" => $this->form->public_id,
            "image_path" => $this->requester->user_img_path
        ];
    }
}

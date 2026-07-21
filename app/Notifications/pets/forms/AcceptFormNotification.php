<?php

namespace App\Notifications\pets\forms;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AcceptFormNotification extends Notification
{
    use Queueable;

    protected $transaction;
    protected $accepter;
    protected $receiver;
    protected $action;
    public function __construct($transaction, $accepter, $receiver, $action)
    {
        $this->transaction = $transaction;
        $this->accepter = $accepter;
        $this->receiver = $receiver;
        $this->action = $action;
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
        $transaction_id = $this->action ? $this->transaction->public_id : null;
        $message = $this->action ? ucfirst($this->accepter->firstName) . " " . ucfirst($this->accepter->lastName) . " accept your form." : ucfirst($this->accepter->firstName) . " " . ucfirst($this->accepter->lastName) . " decline your request.";
        $image_path = $this->accepter->user_img_path;

        return [
            "title" => "acceptForm",
            "message" => $message,
            "transaction_id" => $transaction_id,
            "image_path" => $image_path
        ];
    }
}

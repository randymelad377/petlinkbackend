<?php

namespace App\Notifications\pets\forms;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MarkAsNotification extends Notification
{
    use Queueable;

    protected $history;
    protected $transaction;
    protected $sender;
    protected $receiver;
    protected $action;

    public function __construct($history, $transaction, $sender, $receiver, $action)
    {
        $this->history = $history;
        $this->transaction = $transaction;
        $this->sender = $sender;
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
        $senderName = $notifiable->id === $this->receiver->id ? ucfirst($this->sender->firstName) . " " . ucfirst($this->sender->lastName) :
            ucfirst($this->receiver->firstName) . " " . ucfirst($this->receiver->lastName);

        $message = "";
        $image_path = $notifiable->id === $this->receiver->id ? $this->sender->user_img_path : $this->receiver->user_img_path;

        if (!empty($this->history)) {
            $message = "Your transaction with {$senderName} has been completed, and the transaction has been added to your history.";
        } else if (empty($this->transaction)) {
            $message = "Your transaction with {$senderName} was cancelled.";
        } else if ($this->transaction->ownerMarkAsDone === null && $this->transaction->requesterMarkAsDone === null) {
            $message = "There is a conflict with your transaction with {$senderName}.";
        } else {
            $senderName = ucfirst($this->sender->firstName) . " " . ucfirst($this->sender->lastName);
            $image_path = $this->sender->user_img_path;
            $message = $this->action
                ? "{$senderName} has marked your transaction as completed."
                : "{$senderName} has marked your transaction as cancelled.";
        }

        $object_id = $this->history ? $this->history?->public_id : $this->transaction?->public_id;

        return [
            "title" => "markAs",
            "message" => $message,
            "haveHistory" => (bool) $this->history,
            "object_id" => $object_id,
            "image_path" => $image_path
        ];
    }
}

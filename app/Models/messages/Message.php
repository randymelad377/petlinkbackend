<?php

namespace App\Models\messages;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        "sender_id",
        "message",
        "conversation_id",
        "image_message_path",
        "deleted_by_sender",
        "deleted_by_receiver"
    ];
}

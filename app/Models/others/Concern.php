<?php

namespace App\Models\others;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Concern extends Model
{
    protected $fillable = [
        "user_id",
        "message",
        "image_path",
        "isRead"
    ];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}

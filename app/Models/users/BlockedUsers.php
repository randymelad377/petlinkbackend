<?php

namespace App\Models\users;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BlockedUsers extends Model
{
    protected $fillable = [
        "user_id",
        "blocked_user_id"
    ];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function blocked_user()
    {
        return $this->belongsTo(User::class, "blocked_user_id");
    }
}

<?php

namespace App\Models\messages;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        "user1_id",
        "user2_id",
        "user1_deleted",
        "user2_deleted"
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->public_id = (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function messages()
    {
        return $this->hasMany(Message::class, "conversation_id");
    }

    public function user1()
    {
        return $this->belongsTo(User::class, "user1_id");
    }
    public function user2()
    {
        return $this->belongsTo(User::class, "user2_id");
    }
}

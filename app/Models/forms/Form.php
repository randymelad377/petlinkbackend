<?php

namespace App\Models\forms;

use App\Models\pet\Pets;
use App\Models\transactions\Answers;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = [
        "user_id",
        "pet_id",
        "public_id"
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->public_id = (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function pet()
    {
        return $this->belongsTo(Pets::class, "pet_id");
    }
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
    public function transaction()
    {
        return $this->hasOne(Transaction::class, "form_id");
    }
}
